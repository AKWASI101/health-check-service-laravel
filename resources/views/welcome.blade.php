<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enhanced WELCOME Wave Animation</title>
  <style>
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #2a4d2a, #4d4d00); /* Green-to-gold gradient */
      font-family: 'Segoe UI', Arial, sans-serif;
      overflow: hidden;
    }

    .wave {
      font-size: clamp(2rem, 8vw, 6rem); /* Responsive font size */
      font-weight: 700;
      display: flex;
      gap: 0.5rem;
      color: #ffffff;
      cursor: pointer; /* Indicates interactivity */
    }

    .wave span {
      display: inline-block;
      transform: translateY(0) scale(1);
      transition: transform 0.4s ease, color 0.4s ease, scale 0.4s ease;
    }

    .wave span.active {
      transform: translateY(-40px) scale(1.2); /* Added scale effect */
      color: #00ff00; /* Green */
      filter: drop-shadow(0 0 10px rgba(0, 255, 0, 0.8)); /* Green glow */
    }

    .wave span.active:nth-child(even) {
      color: #ffd700; /* Gold */
      filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.8)); /* Gold glow */
    }

    .wave:hover span {
      transform: translateY(-10px); /* Slight lift on hover */
      color: #00cc00; /* Darker green on hover */
      transition: transform 0.2s ease, color 0.2s ease;
    }

    .wave.paused span.active {
      transform: translateY(0) scale(1); /* Reset active state on pause */
      color: #ffffff;
      filter: none;
    }
  </style>
</head>
<body>
  <div class="wave">
    <span>W</span>
    <span>E</span>
    <span>L</span>
    <span>C</span>
    <span>O</span>
    <span>M</span>
    <span>E</span>
  </div>

  <script>
    const wave = document.querySelector('.wave');
    const letters = document.querySelectorAll('.wave span');
    let index = 0;
    const animationSpeed = 250; // Animation interval in milliseconds
    let intervalId;

    function animateWave() {
      letters.forEach(letter => letter.classList.remove('active'));
      letters[index].classList.add('active');
      index = (index + 1) % letters.length;
    }

    // Start animation
    function startAnimation() {
      intervalId = setInterval(animateWave, animationSpeed);
    }

    // Pause animation on hover
    wave.addEventListener('mouseenter', () => {
      clearInterval(intervalId);
      wave.classList.add('paused');
    });

    // Resume animation on mouse leave
    wave.addEventListener('mouseleave', () => {
      wave.classList.remove('paused');
      startAnimation();
    });

    // Initial start
    startAnimation();
  </script>
</body>
</html>
