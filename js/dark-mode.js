// Check localStorage for the saved theme preference
const savedTheme = localStorage.getItem('theme');
const toggle = document.getElementById('theme-toggle');

// Apply the saved theme preference on page load
if (savedTheme === 'dark') {
  document.documentElement.classList.add('dark');
  if (toggle) toggle.checked = true; // Set the toggle to match the saved preference
} else if (savedTheme === 'light') {
  document.documentElement.classList.remove('dark');
  if (toggle) toggle.checked = false; // Ensure the toggle is unchecked for light mode
}

// Add event listener to save the theme preference
if (toggle) {
  toggle.addEventListener('change', () => {
    if (toggle.checked) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark'); // Save preference as 'dark'
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light'); // Save preference as 'light'
    }
  });
}