document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formResetPassword');

    form.addEventListener('submit', async (event) => {
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.preventDefault();

            const old_password = form.querySelector('input[name="old_password"]').value;
            const new_password = form.querySelector('input[name="new_password"]').value;
            const new_password_conf = form.querySelector('input[name="new_password_conf"]').value;
            const uid = localStorage.getItem('uid');

            try {
                const response = await fetch('http://localhost/happypet/happypet_back/public/api/member_reset_password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ uid, old_password, new_password, new_password_conf }),
                });

                const result = await response.json();

                if (response.ok) {
                    alert('密碼重設成功！');
                    
                    // 確認後跳轉至會員中心頁面
                    window.location.href = '../10_member/member_center.html';
                } else {
                    alert(result.message || '密碼重設失敗，請檢查您的資料並重試。');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('密碼重設時發生錯誤，請稍後再試。');
            }
        }

        form.classList.add('was-validated');
    });
});