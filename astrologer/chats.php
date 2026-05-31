<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = $_SESSION['userid'];

// Fetch all requested chats
$stmt = $conn->prepare("SELECT cs.id, u.name, u.email, cs.start_time 
                        FROM chat_sessions cs 
                        JOIN users u ON cs.user_id=u.id 
                        WHERE cs.astrologer_id=? AND cs.status='requested' 
                        ORDER BY cs.created_at DESC");
$stmt->execute([$astro_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chatsExist = count($chats) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Incoming Chats</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #fafafa; max-width: 480px; margin: auto; transition: 0.3s;}
.chat-card { background: #fff; padding: 15px 20px; border-radius: 10px; margin-bottom: 15px; border-left: 5px solid #10b981; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.btn { display: inline-block; background: #10b981; color: #fff; padding: 10px 20px; margin-top: 10px; border-radius: 8px; text-decoration: none; font-weight: 700; }
.btn-reject { background: #ef4444; }
body.dark-theme { background: #121212; color: #fff; }
body.dark-theme .chat-card { background: #1f2937; }
</style>
</head>
<body>
<script>if(localStorage.getItem('theme') === 'dark') document.body.classList.add('dark-theme');</script>

<h2 style="text-align: center;">Incoming Chat Requests</h2>
<audio id="incomingTone" src="/assets/audio/ringtone.mp3" loop preload="auto" style="display:none;"></audio>

<?php if (!$chatsExist): ?>
    <p style="text-align:center;">No new incoming chats.</p>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tone = document.getElementById("incomingTone");
            tone.pause(); tone.currentTime = 0;
        });
        setInterval(() => window.location.reload(), 3000);
    </script>
<?php else: ?>
    <?php foreach ($chats as $chat): ?>
      <div class="chat-card">
        <div><strong>From:</strong> <?= htmlspecialchars($chat['name'] ?: $chat['email'], ENT_QUOTES, 'UTF-8') ?></div>
        
        <a href="/astrologer/accept_chat.php?session_id=<?= intval($chat['id']) ?>" class="btn">Accept Chat</a>
        <a href="/astrologer/reject_chat.php?session_id=<?= intval($chat['id']) ?>" class="btn btn-reject">Reject</a>
      </div>
    <?php endforeach; ?>
    
    <script>
    async function startTone() {
        try {
            const tone = document.getElementById("incomingTone");
            tone.currentTime = 0;
            await tone.play();
        } catch(e) { console.log("Audio blocked"); }
    }
    document.addEventListener("DOMContentLoaded", startTone);
    // Auto-refresh even while ringing so if user cancels, it disappears
    setInterval(() => window.location.reload(), 4000); 
    </script>
<?php endif; ?>
</body>
</html>