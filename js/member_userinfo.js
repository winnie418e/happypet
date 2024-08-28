// let uid = 1; // 假設使用者的 UID 為 1

// 發送請求以獲取使用者資訊
fetch('http://localhost/happypet/happypet_back/public/api/member_userinfo', {
    method: 'post',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ uid: uid })
})
.then(response => {
    if (!response.ok) {
        throw new Error(`伺服器錯誤(fetch回傳有問題): ${response.statusText}`);
    }
    return response.json();
})
.then(data => {
    console.log('接收到的使用者資料:', data);
    // console.log(data[0].cname);
    // console.log(data.user_name);
    
    
    // 將接收到的資料填入表單
    document.getElementById('user_name').value = data[0].cname;
    document.getElementById('user_email').value = data[0].email;
    document.getElementById('user_phone1').value = data[0].cellphone;
    document.getElementById('user_phone2').value = data[0].cellphone2;
    document.getElementById('user_address').value = data[0].address;

    // 根據性別設定性別的選項
    const genderInputs = document.querySelectorAll('input[name="user_gender"]');
    genderInputs.forEach(input => {
        if (input.value === data[0].sex) {
            input.checked = true;
        }
    });
})
.catch(error => {
    console.error("錯誤:", error);
    // 顯示錯誤消息
});

document.getElementById('btnEditUserInfo').onclick = (event) => {
    event.preventDefault();

    let form = document.getElementById('formUserEdit');
    let formData = new FormData(form);
    formData.append('uid', 1); // 假設你的 `uid` 是 1，你可以根據情況動態設置

    fetch('http://localhost/happypet/happypet_back/public/api/member_userinfo_update', {
        method: 'post',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`伺服器錯誤: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.message === "資料更新成功！") {
            alert("資料已更新");
        } else {
            alert("更新失敗，請稍後再試");
        }
    })
    .catch(error => {
        console.error("錯誤:", error);
    });
}

document.getElementById('btnLeaveEdit').onclick = function() {
    window.location.href = '../10_member/member_center.html';
};