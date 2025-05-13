// تحقق من صحة النموذج
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const errors = [];
    
    // التحقق من الحقول المطلوبة
    if(!formData.get('name')) errors.push('الرجاء إدخال الاسم');
    if(!formData.get('email').includes('@')) errors.push('البريد الإلكتروني غير صحيح');
    
    if(errors.length > 0) {
        alert(errors.join('\n'));
    } else {
        // إرسال النموذج
        this.reset();
        alert('تم إرسال الرسالة بنجاح!');
    }
});