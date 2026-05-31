<div id="ratingModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[9999] flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-sm w-full shadow-2xl transform transition-all text-center">
        <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Rate Your Session</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">How was your consultation?</p>

        <div class="flex justify-center gap-2 mb-6" id="starContainer">
            <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="1"></i>
            <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="2"></i>
            <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="3"></i>
            <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="4"></i>
            <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="5"></i>
        </div>

        <input type="hidden" id="selectedRating" value="0">
        
        <textarea id="reviewText" rows="3" placeholder="Write a short review (optional)..." 
                  class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:border-black outline-none mb-6 resize-none"></textarea>

        <button onclick="submitReview()" id="submitReviewBtn" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg transition-all disabled:opacity-50">
            Submit Review
        </button>
    </div>
</div>

<script>
    // Star Hover and Click Logic
    const stars = document.querySelectorAll('#starContainer i');
    const ratingInput = document.getElementById('selectedRating');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            let val = this.getAttribute('data-val');
            ratingInput.value = val;
            updateStars(val);
        });
    });

    function updateStars(val) {
        stars.forEach(s => {
            if (s.getAttribute('data-val') <= val) {
                s.classList.remove('text-gray-300');
                s.classList.add('text-yellow-400');
            } else {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            }
        });
    }

    // Call this function when the call ends successfully
    function showRatingModal() {
        document.getElementById('ratingModal').classList.remove('hidden');
    }

    // Submit Review AJAX
    async function submitReview() {
        const rating = ratingInput.value;
        const text = document.getElementById('reviewText').value;
        
        // Assuming current_call_id and current_astro_id are available in your frontend scope
        if (rating == 0) {
            alert('Please select a star rating.');
            return;
        }

        document.getElementById('submitReviewBtn').innerText = "Submitting...";

        const formData = new FormData();
        formData.append('call_id', current_call_id); 
        formData.append('astro_id', current_astro_id);
        formData.append('rating', rating);
        formData.append('review', text);

        try {
            const response = await fetch('/user/submit_review.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.text();
            
            if(result === "SUCCESS") {
                document.getElementById('ratingModal').innerHTML = `<h2 class="text-2xl font-black text-emerald-500 mb-2">Thank You!</h2><p class="text-gray-500">Your feedback helps us improve.</p><button onclick="window.location.href='/user/dashboard.php'" class="mt-6 w-full py-4 bg-gray-900 text-white font-bold rounded-xl">Go Home</button>`;
            } else {
                alert("Error: " + result);
                document.getElementById('submitReviewBtn').innerText = "Submit Review";
            }
        } catch (e) {
            alert("Network Error");
        }
    }
</script>