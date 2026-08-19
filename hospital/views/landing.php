<?php
$pageTitle = 'MediBook — Modern Hospital Appointment Booking';
require BASE_PATH . '/views/layouts/header.php';
?>

<style>
.hero{background:linear-gradient(135deg,var(--navy) 0%,#1a3a6e 50%,#0d2954 100%);min-height:90vh;display:flex;align-items:center;padding:60px 24px;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");}
.hero-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1;}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(10,138,122,.25);border:1px solid rgba(10,138,122,.4);color:var(--teal-lt);padding:6px 16px;border-radius:30px;font-size:.82rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
.hero h1{font-size:3.2rem;color:var(--white);line-height:1.15;margin-bottom:20px;}
.hero h1 em{color:var(--teal-lt);font-style:normal;}
.hero p{color:rgba(255,255,255,.7);font-size:1.05rem;margin-bottom:32px;line-height:1.7;}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap;}
.hero-visual{display:flex;flex-direction:column;gap:12px;}
.hero-card{background:rgba(255,255,255,.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:20px 24px;color:var(--white);}
.hero-card-icon{font-size:2rem;margin-bottom:10px;}
.hero-card-title{font-size:1rem;font-family:'DM Serif Display',serif;margin-bottom:4px;}
.hero-card-text{font-size:.85rem;color:rgba(255,255,255,.6);}
.hero-card-mini{display:flex;align-items:center;gap:12px;}
.hero-card-mini .dot{width:10px;height:10px;border-radius:50%;background:var(--teal-lt);flex-shrink:0;}

.features{padding:80px 24px;background:var(--cream);}
.features-grid{max-width:1200px;margin:40px auto 0;display:grid;grid-template-columns:repeat(3,1fr);gap:24px;}
.feature-card{background:var(--white);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow-sm);text-align:center;}
.feature-icon{width:64px;height:64px;border-radius:16px;background:rgba(10,138,122,.1);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 18px;}
.feature-card h3{margin-bottom:10px;font-size:1.1rem;}
.feature-card p{color:var(--gray-600);font-size:.9rem;line-height:1.6;}

.section-label{text-align:center;font-size:.8rem;text-transform:uppercase;letter-spacing:.1em;color:var(--teal);font-weight:700;margin-bottom:8px;}
.section-title{text-align:center;font-size:2rem;margin-bottom:8px;}
.section-sub{text-align:center;color:var(--gray-600);max-width:500px;margin:0 auto;}

.cta{background:var(--navy);padding:70px 24px;text-align:center;}
.cta h2{color:var(--white);font-size:2.2rem;margin-bottom:14px;}
.cta p{color:rgba(255,255,255,.65);margin-bottom:28px;}

@media(max-width:768px){
  .hero-inner{grid-template-columns:1fr;}
  .hero h1{font-size:2.2rem;}
  .features-grid{grid-template-columns:1fr;}
}
</style>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <div class="hero-eyebrow">🏥 Trusted Healthcare Scheduling</div>
      <h1>Book Your <em>Doctor's Visit</em> in Minutes</h1>
      <p>MediBook connects patients with top-rated specialists. Browse doctors, check real-time availability, and confirm your appointment — all from one place.</p>
      <div class="hero-btns">
        <a href="<?= BASE_URL ?>/index.php?page=register" class="btn btn-primary btn-lg">Get Started Free</a>
        <a href="<?= BASE_URL ?>/index.php?page=login" class="btn btn-outline btn-lg" style="border-color:rgba(255,255,255,.4);color:white;">Sign In</a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-card">
        <div class="hero-card-icon">📅</div>
        <div class="hero-card-title">Smart Scheduling</div>
        <div class="hero-card-text">Real-time slot availability — no double bookings ever.</div>
      </div>
      <div class="hero-card hero-card-mini">
        <div class="dot"></div>
        <div>
          <div style="font-size:.9rem;font-weight:600;">Dr. Sarah Malik — Cardiology</div>
          <div style="font-size:.8rem;color:rgba(255,255,255,.55);">Next available: Today at 11:00 AM</div>
        </div>
      </div>
      <div class="hero-card hero-card-mini">
        <div class="dot" style="background:#D97706;"></div>
        <div>
          <div style="font-size:.9rem;font-weight:600;">Appointment #1042 — Confirmed</div>
          <div style="font-size:.8rem;color:rgba(255,255,255,.55);">Dr. Ahmed Hassan · May 15 at 2:30 PM</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="container">
    <div class="section-label">Why MediBook?</div>
    <h2 class="section-title">Everything you need for seamless care</h2>
    <p class="section-sub mt-1">From booking to follow-up, we handle the scheduling so you can focus on health.</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🔍</div>
      <h3>Browse Specialists</h3>
      <p>Filter doctors by specialization, view their profiles, fees, and weekly availability at a glance.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⚡</div>
      <h3>Instant Booking</h3>
      <p>Select an available time slot and confirm your appointment in seconds. Receive your booking ID immediately.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <h3>Full Dashboard</h3>
      <p>Patients, doctors, and admins each get a tailored dashboard to manage appointments end-to-end.</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta">
  <h2>Ready to book your first appointment?</h2>
  <p>Join thousands of patients managing their healthcare online.</p>
  <a href="<?= BASE_URL ?>/index.php?page=register" class="btn btn-primary btn-lg">Create Free Account</a>
</section>

<?php require BASE_PATH . '/views/layouts/footer.php'; ?>
