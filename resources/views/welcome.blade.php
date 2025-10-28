<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Welcome — RocketHR HRM System</title>

  <!-- Google Fonts: Poppins & Nunito -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <!-- AOS (Animate On Scroll) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

  <!-- Custom CSS -->
  <style>
    :root {
      --primary-color: #5A67D8;
      --secondary-color: #434190;
      --accent-color: #2B6CB0;
      --light-bg: #f9f9f9;
      --text-color: #333;
    }
    body {
      font-family: 'Poppins', 'Nunito', sans-serif;
      background: var(--light-bg);
      color: var(--text-color);
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }
    /* HERO SECTION */
    .hero {
      min-height: 100vh;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 20px;
      color: #fff;
    }
    .hero h1 {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 1.25rem;
      margin-bottom: 30px;
      max-width: 600px;
    }
    .btn-cta {
      background-color: #fff;
      color: var(--secondary-color);
      padding: 12px 40px;
      border: none;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .btn-cta:hover {
      background-color: var(--accent-color);
      color: #fff;
      transform: scale(1.05);
    }
  </style>
</head>
<body>

<!-- HERO SECTION -->
<section class="hero">
  <h1 class="animate__animated animate__fadeInDown">Welcome to RocketHR HRM System</h1>
  <p class="animate__animated animate__fadeInUp">
    Manage your workforce efficiently and effectively with RocketHR — your complete HRM solution.
  </p>
  <a href="{{ route('login') }}" class="btn btn-cta animate__animated animate__zoomIn">Get Started</a>
</section>

<!-- FEATURES SECTION -->
{{-- <section class="section bg-light" id="features">
  <div class="container">
    <h2 class="section-title text-center" data-aos="fade-up">Our Features</h2>
    <div class="row g-4">
      <div class="col-md-4" data-aos="flip-up">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-users"></i>
          <h5 class="card-title mt-3">Employee Management</h5>
          <p class="card-text">
            Seamlessly manage employee data, payroll, and attendance.
          </p>
        </div>
      </div>
      <div class="col-md-4" data-aos="flip-up" data-aos-delay="100">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-briefcase"></i>
          <h5 class="card-title mt-3">Department & Roles</h5>
          <p class="card-text">
            Organize your workforce with departments and role-based access.
          </p>
        </div>
      </div>
      <div class="col-md-4" data-aos="flip-up" data-aos-delay="200">
        <div class="card feature-card p-4 text-center">
          <i class="fas fa-calendar-check"></i>
          <h5 class="card-title mt-3">Time Tracking & Leaves</h5>
          <p class="card-text">
            Monitor work hours and manage leave applications effortlessly.
          </p>
        </div>
      </div>
    </div>
  </div>
</section> --}}

<!-- FOOTER -->
<footer class="bg-dark text-white text-center py-3">
  <p>&copy; {{ date('Y') }} RocketHR HRM System. All rights reserved.</p>
</footer>

</body>
</html>
