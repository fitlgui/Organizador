module.exports = {
  content: [
    './index.php',
    './views/**/*.php',
    './app/**/*.php',
    './script.js'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui']
      },
      colors: {
        tuiublue: '#00A3FF',
        tuiured: '#FF3B4F',
        ink: '#07111F'
      }
    }
  }
};
