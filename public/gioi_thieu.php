<?php
require_once __DIR__ . '/../functions.php';
include __DIR__ . '/../layout/header.php';
?>
    <style>
        .hero-section {
            height: 100vh;
            background: linear-gradient(135deg, rgba(20, 30, 48, 0.8), rgba(36, 59, 85, 0.6)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><pattern id="crowd" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="%23ffffff" opacity="0.3"/><circle cx="50" cy="40" r="2" fill="%23ffffff" opacity="0.2"/><circle cx="80" cy="30" r="2" fill="%23ffffff" opacity="0.4"/><circle cx="30" cy="60" r="2" fill="%23ffffff" opacity="0.3"/><circle cx="70" cy="80" r="2" fill="%23ffffff" opacity="0.2"/></pattern></defs><rect width="100%" height="100%" fill="url(%23crowd)"/><rect x="0" y="600" width="1200" height="200" fill="%231a2332" opacity="0.8"/></svg>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 215, 0, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(0, 191, 255, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255, 20, 147, 0.3) 0%, transparent 50%),
                linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
            animation: colorShift 8s ease-in-out infinite alternate;
        }

        @keyframes colorShift {
            0% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .stage-lights {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse 300px 100px at 25% 10%, rgba(255, 255, 255, 0.2) 0%, transparent 50%),
                radial-gradient(ellipse 300px 100px at 75% 15%, rgba(255, 255, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse 200px 80px at 50% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: stageLights 6s ease-in-out infinite alternate;
        }

        @keyframes stageLights {
            0% { opacity: 0.5; transform: translateY(0); }
            100% { opacity: 0.8; transform: translateY(-10px); }
        }

        .content {
            z-index: 2;
            max-width: 900px;
            padding: 0 20px;
            animation: fadeInUp 1.2s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-text {
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 30px;
            opacity: 0.9;
            text-transform: uppercase;
        }

        .main-title {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            line-height: 0.9;
            margin-bottom: 40px;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.7);
            letter-spacing: -2px;
        }

        .main-title .line {
            display: block;
            animation: slideInLeft 1s ease-out;
        }

        .main-title .line:nth-child(2) {
            animation-delay: 0.2s;
        }

        .main-title .line:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            padding: 18px 40px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
        }

        .footer-text {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            opacity: 0.7;
            z-index: 2;
        }

        .footer-text a {
            color: #FFD700;
            text-decoration: none;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 6s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .brand-text {
                font-size: 14px;
            }
            
            .main-title {
                font-size: clamp(2rem, 10vw, 4rem);
            }
            
            .cta-button {
                padding: 15px 30px;
                font-size: 14px;
            }
        }
    </style>
    <section class="hero-section">
        <div class="stage-lights"></div>
        <div class="particles" id="particles"></div>
        
        <div class="content">
            <h1>
                GIỚI THIỆU VỀ EVENTSPLANT
            </h1>
            <h2>
Trang web EventsPlant là nền tảng quản lý sự kiện toàn diện, giúp doanh nghiệp, tổ chức và cá nhân dễ dàng tổ chức, theo dõi và tối ưu hóa mọi hoạt động liên quan đến sự kiện. Với giao diện trực quan, thao tác đơn giản và các tính năng hiện đại như quản lý khách mời, lịch trình, vé mời và báo cáo thống kê, EventsPlant mang đến giải pháp nhanh chóng, tiết kiệm thời gian và chi phí. Chúng tôi cam kết đồng hành cùng bạn để mỗi sự kiện không chỉ diễn ra suôn sẻ mà còn để lại ấn tượng chuyên nghiệp, thành công vượt trội.

            </h2>
            <a href="<?= $cfg_base_url ?>/public/dich_vu.php" class="cta-button">Dịch vụ</a>
        </div>
            </section>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Smooth scrolling for CTA button
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            // Add some dynamic lighting effects
            const hero = document.querySelector('.hero-section');
            let mouseX = 0, mouseY = 0;
            
            document.addEventListener('mousemove', (e) => {
                mouseX = e.clientX / window.innerWidth;
                mouseY = e.clientY / window.innerHeight;
                
                hero.style.background = `
                    radial-gradient(circle at ${mouseX * 100}% ${mouseY * 100}%, rgba(255, 215, 0, 0.15) 0%, transparent 50%),
                    linear-gradient(135deg, rgba(20, 30, 48, 0.8), rgba(36, 59, 85, 0.6)),
                    url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><pattern id="crowd" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="%23ffffff" opacity="0.3"/><circle cx="50" cy="40" r="2" fill="%23ffffff" opacity="0.2"/><circle cx="80" cy="30" r="2" fill="%23ffffff" opacity="0.4"/><circle cx="30" cy="60" r="2" fill="%23ffffff" opacity="0.3"/><circle cx="70" cy="80" r="2" fill="%23ffffff" opacity="0.2"/></pattern></defs><rect width="100%" height="100%" fill="url(%23crowd)"/><rect x="0" y="600" width="1200" height="200" fill="%231a2332" opacity="0.8"/></svg>')
                `;
            });
        });
    </script>
<?php include __DIR__ . '/../layout/footer.php'; ?>
