let insertBtn = document.getElementById('insertBtn');
window.onload = ()=>{
    let datObj = new Date()
    let dateFormat = `${ datObj.getFullYear() }-${ (datObj.getMonth()+1).toString().padStart(2,0) }-${ (datObj.getDate()).toString().padStart(2,0) }`
    console.log(dateFormat)
    restockDate.value = dateFormat

    function showMsg(msg){
        $('#myModal').modal('show')
        $('#alertMsg').text(msg)
    }
    $('.bi-search').click(()=>{
        pdName.innerHTML = '<span class="position-relative">查詢中<i class="bi bi-battery position-absolute"></i><i class="bi bi-battery-half position-absolute"></i><i class="bi bi-battery-full position-absolute"></i></span>';
        let pdWarehouseFormData = new FormData(document.getElementById('pdWarehouse'));
        pdWarehouseFormData.append('action', 'fetch');
        console.log('pdWarehouseFormData',pdWarehouseFormData)
        console.log('productID.value',productID.value)
       // productID.onchange = ()=>{
        fetch(`http://localhost/happypet/happypet_back/public/api/product_back/warehouse `,{
        // fetch(`http://localhost/testpet/public/api/product_back/warehouse `,{
            method:'post',
            body:pdWarehouseFormData
        })
        .then(response=>{
            // console.log(response)
            // return response.text()
            return response.json()
        })
        .then(data=>{
            console.log('我是data',data)
            data.full_name ? pdName.innerText = data.full_name :  pdName.innerText = data.error;
            
        })

    })
    // }
   
    insertBtn.onclick = (event)=>{
        event.preventDefault();
        let pdWarehouseFormData = new FormData(document.getElementById('pdWarehouse'));
        pdWarehouseFormData.append('action', 'insert');
        // fetch('http://localhost/testpet/public/api/product_back/warehouse',{
        fetch('http://localhost/happypet/happypet_back/public/api/product_back/warehouse',{
            method:'post',
            body:pdWarehouseFormData
        })
        .then(response=>{
            if (!response.ok) {
                throw new Error(`伺服器錯誤: ${response.statusText}`);
            }
            // console.log(response)
            // return response.text()
            return response.json()
        })
        .then(data=>{
            // alert(data)
            if (data.message) {
                console.log(data.message);
                showMsg(data.message)
                setTimeout(()=>{
                    // location.reload()// 刷新頁面
                },1500)
            } else if (data.error) {
                showMsg(data.error)
            }

           
        })
    }
}