// تفعيل القائمة المتنقلة
document.querySelector('.menu-toggle').addEventListener('click', () => {
    document.querySelector('.nav-list').classList.toggle('active');
});

// تأثير العدادات
const counters = document.querySelectorAll('[data-count]');
const animationDuration = 2000; // 2 ثانية

counters.forEach(counter => {
    const target = parseInt(counter.getAttribute('data-count'));
    let count = 0;
    
    const increment = target / (animationDuration / 10);
    
    const updateCount = () => {
        if (count < target) {
            count += increment;
            counter.textContent = Math.ceil(count);
            setTimeout(updateCount, 10);
        } else {
            counter.textContent = target;
        }
    };
    
    updateCount();
});

// التمرير السلس للروابط
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        targetSection.scrollIntoView({
            behavior: 'smooth'
        });
    });
});