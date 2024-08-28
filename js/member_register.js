
document.getElementById('btnRegister').onclick = (event) => {
    event.preventDefault();

    // 獲取表單元素
    let form = document.getElementById('myregister');

    // 手動觸發表單驗證
    form.classList.add('was-validated');

    // 確認表單驗證成功後才執行API呼叫
    if (form.checkValidity()) {
        let formData = new FormData(form);

        fetch('http://localhost/happypet/happypet_back/public/api/member_register', {
            method: 'post',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`伺服器錯誤(fetch回傳有問題): ${response.statusText}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data.message) {
                    showModel(data.message, 'success');

                } else {
                    showModel(data.error, 'error');
                }
            })
            .catch(error => {
                showModel(`發生錯誤: ${error.message}`, 'error');
            });
    }

    function showModel(message, type) {
        $('#myModal').modal('show');
        alert_message.innerText = message;
        alert_message.classList.remove('text-success', 'text-danger');
        alert_message.classList.add(type === 'success' ? 'text-success' : 'text-danger');
        if (type === 'success') {
            setTimeout(() => {
                window.location.href = '../00_index/index.html'; // 更改為你的登入頁面路徑
            }, 3000); // 3 秒鐘後重導
        }
    }

}

// btnRegister.onclick = (event) => {
//     event.preventDefault();
//     let formData = new FormData(document.getElementById('myregister'));
//     fetch('http://localhost/happypet/happypet_back/public/api/member_register', {
//         method: 'post',
//         body: formData
//     })
//         .then(response => {
//             console.log(response);
//             if (!response.ok) {
//                 throw new Error(`伺服器錯誤(fetch回傳有問題): ${response.statusText}`);
//             }
//             // return response.text()
//             return response.json()
//         })
//         .then((data) => {
//             console.log('我是data1', data.message)
//             if (data.message){
//                 showModel( data.message)
//             }else{
//                 showModel( data.error)
//             }
//             // alert_message.innerText = data.message;
//         })


//         function showModel(message){
//             $('#myModal').modal('show')
//             alert_message.innerText = message;
//         }
//     }

