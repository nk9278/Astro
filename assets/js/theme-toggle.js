// Select the theme toggle button and the icon container
const themeToggleBtn = document.getElementById('themeToggle');
const sunIcon = document.getElementById('sunIcon');
const moonIcon = document.getElementById('moonIcon');

// Function to apply dark mode styles
function enableDarkMode() {
  document.body.classList.add('dark-theme');
  sunIcon.style.display = 'none';
  moonIcon.style.display = 'inline-block';
  localStorage.setItem('theme', 'dark');
}

// Function to apply light mode styles
function enableLightMode() {
  document.body.classList.remove('dark-theme');
  sunIcon.style.display = 'inline-block';
  moonIcon.style.display = 'none';
  localStorage.setItem('theme', 'light');
}

// Check saved theme in localStorage and apply it on page load
function loadTheme() {
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'dark') {
    enableDarkMode();
  } else {
    enableLightMode();
  }
}

// Toggle between dark and light mode on button click
themeToggleBtn.addEventListener('click', () => {
  if (document.body.classList.contains('dark-theme')) {
    enableLightMode();
  } else {
    enableDarkMode();
  }
});

// Apply the saved theme when the page loads
document.addEventListener('DOMContentLoaded', loadTheme);
