<?php
require_once __DIR__ . '/../functions.php';
$ds = banners_all();
include __DIR__ . '/../layout/header.php';
?>

<style>
.slider-wrap {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
}

.slider-track {
    display: flex;
    transition: transform .5s ease;
}

.slide {
    flex: 0 0 100%;
    height: 850px;
    min-height: 800px;
    position: relative;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.slider-dots {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 14px;
    display: flex;
    gap: 8px;
    justify-content: center;
}

.slider-dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .55);
    border: 1px solid rgba(0, 0, 0, .15);
    cursor: pointer;
}

.slider-dot.active {
    background: #fff;
}

.slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(15, 23, 42, .55);
    color: #fff;
    border: 0;
    width: 42px;
    height: 42px;
    border-radius: 999px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.slider-prev {
    left: 12px;
}

.slider-next {
    right: 12px;
}
</style>

<div class="the" style="padding:0; overflow:hidden">
    <div class="slider-wrap" id="banner-slider">
        <div class="slider-track">
            <?php foreach ($ds as $b): ?>
            <div class="slide">
                <img src="<?= htmlspecialchars($b) ?>" alt="banner">
            </div>
            <?php endforeach; ?>
            <?php if (empty($ds)): ?>
            <div class="slide" style="display:flex;align-items:center;justify-content:center;background:#f1f5f9">
                <div class="nho">Chưa có banner. Hãy thêm trong tab Cài đặt.</div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($ds)): ?>
        <button class="slider-arrow slider-prev" aria-label="Prev">‹</button>
        <button class="slider-arrow slider-next" aria-label="Next">›</button>
        <div class="slider-dots">
            <?php foreach (array_keys($ds) as $i): ?>
            <button class="slider-dot <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const box = document.getElementById('banner-slider');
    if (!box) return;
    const track = box.querySelector('.slider-track');
    const slides = box.querySelectorAll('.slide');
    const dots = box.querySelectorAll('.slider-dot');
    const prev = box.querySelector('.slider-prev');
    const next = box.querySelector('.slider-next');
    let idx = 0,
        N = slides.length,
        timer;

    function go(i) {
        if (!N) return;
        idx = (i + N) % N;
        track.style.transform = `translateX(-${idx*100}%)`;
        dots.forEach((d, j) => d.classList.toggle('active', j === idx));
    }

    function auto() {
        clearInterval(timer);
        timer = setInterval(() => go(idx + 1), 4000);
    }
    prev?.addEventListener('click', () => {
        go(idx - 1);
        auto();
    });
    next?.addEventListener('click', () => {
        go(idx + 1);
        auto();
    });
    dots.forEach(d => d.addEventListener('click', () => {
        go(+d.dataset.idx);
        auto();
    }));
    auto();
})();
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>