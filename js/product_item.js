window.onload = ()=>{
    let goodsDescription = document.getElementById('goods_description')
    let mainImg = document.getElementById('main_img')
    let goodsTitle = document.getElementById('goods_title')
    let addCartBtn = document.getElementById('add_cart')
    let currentPage = document.querySelector('li[aria-current="page"]')
    let flavorOrColorArea = document.querySelector('.flavorOrColorArea')
    let weightOrSizeArea = document.querySelector('.weightOrSizeArea')
    let thumbnail = document.querySelector('.thumbnail')
    let pdDescImg = document.querySelector('.pdDescImg')
    // let choose = document.querySelectorAll('.choose')

    let myModal = document.getElementById('myModal')
    let myInput = document.getElementById('myInput')

    myModal.addEventListener('shown.bs.modal', function () {
        myInput.focus()
    })
    function showMsg(msg){
        $('#myModal').modal('show')
        $('#alertMsg').text(msg)
    }

    let pdNavbar = document.querySelector('.pd_navbar');
    // let logo = document.querySelector('.logo')
    window.addEventListener('resize',function(){
        
        if (window.innerWidth <= 1198) {
            pdNavbar.style.top = "148px"
        } else {
            pdNavbar.style.top = "96px"
        }
        // console.log('window.innerWidth',window.innerWidth)
    })
    // ------------------------------------------
    // url測試
    let urlParams  = new URLSearchParams(window.location.search)
    // console.log('window',window.location.search)
    let categoryID = urlParams.get('category') 
    let seriesID = urlParams.get('sID') 
    // console.log(categoryID,seriesID)




    // ------------------------------------------

    // console.log('flavorOrColorArea',flavorOrColorArea.className)
    // fetch(`http://localhost/testpet/public/product/ds/96`,{
    // 查詢此產品資訊
    // fetch(`http://localhost/testpet/public/product/${categoryID}/${seriesID}`,{
    fetch(`http://localhost/happypet/happypet_back/public/api/product/${categoryID}/${seriesID}`,{
        method:'get',
    })
    .then(response=>{
        // console.log(response)
        // return response.text()   //圖片
        return response.json()  //陣列
    })
    .then(data=>{
        // console.log(data)
        let {products,productImgs,categoryName} = data
        // console.log('第一個',products)

        // 過濾下架產品
        // products = products.filter(product => product.status === 't');
        // products = products.filter(product => product.status == 1);
        products = products.filter(product => product.shelves_status == 1);
        if (products.length === 0) {
            // 如果沒有上架產品，顯示提示或進行其他處理
            alert('產品準備中');
            return;
        }

        // console.log('第二個',products)

        // console.log(productImgs)
        $('.QuantityArea').removeClass('d-none')
        $('.QuantityArea').addClass('d-flex')
        $('.breadcrumb').removeClass("d-none")
        $('.choose').removeClass("d-none")
    
        // 取系列名到標題、麵包屑
        // console.log($('.breadcrumb').find('a').eq(1).text())
        // console.log($('.breadcrumb').find('a')[1].innerText)
        currentPage.innerText = products[0].series_name
        $('.breadcrumb').find('a').eq(1).text(categoryName)
        goodsTitle.innerText = products[0].series_name
        goodsTitle.setAttribute('data-categoryID',products[0].category_id)
        mainImg.src = products[0].cover_img
        let categoryID = goodsTitle.getAttribute('data-categoryid')
        $('.breadcrumb').find('a').eq(1).prop('href',`http://localhost/happypet/happypet_front/40_product/front/product.html?category=${categoryID}`)
       
        productImgs.forEach((productImg,i)=>{
            let {pic_category_id} = productImg
            if(pic_category_id == 1 || pic_category_id == 2){
                // goodImgArr.push(productImg.img)
                let pdImg = document.createElement('img')
                pdImg.setAttribute('src',productImg.img) 
                thumbnail.appendChild(pdImg)
                pdImg.onclick = ()=>{
                    // console.log('pdImg = ',pdImg)
                    mainImg.src = pdImg.src
                }
                // thumbnail.innerHTML += `<img src="${productImg.img}" alt="">`

            }else{
                pdDescImg.innerHTML += `<img class="col-12" src="${productImg.img}" alt="">`
            }
        })
        //  數量增減
        let currentQuantity = ''
        plusBtn.onclick = ()=>{
            currentQuantity = parseInt(pdQuantity.value) + 1
            pdQuantity.value = currentQuantity
            // console.log(pdQuantity.value)
        }
        minusBtn.onclick = ()=>{
            currentQuantity = parseInt(pdQuantity.value) -1
            pdQuantity.value = currentQuantity
            pdQuantity.value >= 1 || (pdQuantity.value = 1)  
            // console.log(pdQuantity.value)
        }
        
        let descriptionSet = new Set()
        // console.log('productsssssssss',products)
        products.forEach((product) => {
            // console.log(product)
            for(let i = 1; i <= 5 ; i++){
                let key = `description${i}`
                if(key.startsWith('description')){ 
                    descriptionSet.add(product[key])
                }
                
            }
            // let {flavor,color,weight,size} = product
            // console.log('color',color)
        });

        let descriptionArr = Array.from(descriptionSet)
        // console.log(descriptionArr)
        descriptionArr.forEach((description)=>{
            // console.log(description)
            let descriptionli = document.createElement('li')
            descriptionli.innerText = description
            goodsDescription.appendChild(descriptionli)
            
        })

        // console.log('SellingProduct',SellingProducts)
        // 根據取的資料變成選項按鈕
        // let flavorArr = products.reduce(function(arr,{flavor,...items}){
        let flavorArr = products.reduce(function(arr,{flavor}){
        // let flavorArr = SellingProducts.reduce(function(arr,{flavor}){
            // console.log('arr',arr)
            // return arr.indexOf(flavor) == -1 ? [...arr,flavor] : arr
            if (flavor && arr.indexOf(flavor) == -1){
                arr.push(flavor);
            } 
            return arr
        },[])
        // let colorArr = products.reduce(function(arr,{color,...items}){

        
        let styleArr = products.reduce(function(arr,{style}){
        // let colorArr = SellingProducts.reduce(function(arr,{color}){
            // return arr.indexOf(color) == -1 ? [...arr,color] : arr
            if (style && arr.indexOf(style) == -1){
                arr.push(style);
            }
            return arr
        },[])
        // let weightArr = products.reduce(function(arr,{weight,...items}){
        let weightArr = products.reduce(function(arr,{weight}){
        // let weightArr = SellingProducts.reduce(function(arr,{weight}){
            // return arr.indexOf(weight) == -1 ? [...arr,weight] : arr
            if (weight && arr.indexOf(weight) == -1){
                arr.push(weight);
            }
            return arr
        },[])
        // let sizeArr = products.reduce(function(arr,{size,...items}){
        let sizeArr = products.reduce(function(arr,{size}){
        // let sizeArr = SellingProducts.reduce(function(arr,{size}){
            // return arr.indexOf(size) == -1 ? [...arr,size] : arr
            if (size && arr.indexOf(size) == -1){
                arr.push(size);
            }
            return arr
        },[])
        // console.log('flavorArr',flavorArr)
        // console.log('colorArr',colorArr)  //要改款式!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        // console.log('weightArr',weightArr)
        // console.log("sizeArr",sizeArr)
        // let str = "flavorArr"
        
        // console.log('flavorArr',flavorArr)

        if(flavorArr.length > 0){
            traverseArray(flavorArr,flavorOrColorArea,"flavor")
        }else if(styleArr.length > 0){
            // traverseArray(colorArr,flavorOrColorArea,"color")
            traverseArray(styleArr,flavorOrColorArea,"style")
        }

        if(weightArr.length > 0){
            traverseArray(weightArr,weightOrSizeArea,"weight")
        }else if(sizeArr.length > 0){
            traverseArray(sizeArr,weightOrSizeArea,"size")
        }

        
        
        let productPrice = document.querySelector('#price span')
        // let categoryID = goodsTitle.getAttribute('data-categoryid')

        let flavorOrColor;
        let weightOrSize;
        // console.log('categoryID',categoryID)
        // flavorOrColorInputs[0].setAttribute('checked',true)
        // weightOrSizeInputs[0].setAttribute('checked',true)


        // ----------------------------------------------------------------------------------------------
            
            // console.log('我是input',flavorOrColorInputs[0])
            // console.log('我是products',products)
            // console.log('被選中的',document.querySelector('.flavorOrColorArea input[type="radio"]:checked'))
            // flavorOrColorInputs.forEach((input)=>{
            //     input.addEventListener('change',function(){
            //         console.log('監聽input',input.value)
            //     })
            // })
            
        if(categoryID == 'ds' || categoryID == 'cs'){
            // flavorOrColor = 'color'
            flavorOrColor = 'style'
            weightOrSize = 'size'
        }else{
            flavorOrColor = 'flavor'
            weightOrSize = 'weight'
        }
        // console.log('種類',flavorOrColor,weightOrSize)
        document.querySelectorAll('.flavorOrColorArea input,.weightOrSizeArea input').forEach(input => {
            input.addEventListener('change',checkInput)
            
        });
        
        
    //    
        
       
        // ----------------------------------------------------------------------------------------------
        // 新增選擇按鈕( 口味、款式 | 淨重、尺寸 )
        function traverseArray(arr,area,type){
            arr.forEach((item,i)=>{
                area.innerHTML += ` 
                    <input type="radio" class="d-none" id="${area.className}${i}" class="flavorOrColor" name="${type}" value="${item}" >
                    <label for="${area.className}${i}">${item}</label>
                `
            })
        }
       
        // 取得產品資訊後新增購物車
        function getProductInfo(user,poductID,quantity){
            let addCartText = document.querySelector('#add_cart p')
            let addCartIcon = document.querySelector('#add_cart i.bi-cart-fill')
            let addCartCheckIcon = document.querySelector('#add_cart i.bi-cart-check-fill')

            // console.log('fn裡的poductID',poductID)
            // console.log('fn裡的quantity',quantity)
            if(!poductID || !quantity){
                // console.log('尚未選擇產品')
                showMsg('尚未選擇產品')
                setTimeout(() => {
                    $('#myModal').modal('hide')
                }, 1500);

            }else{
                addCartText.style.opacity = "0"
                addCartIcon.style.opacity = "1"
                console.log('我是user',user,'我是poductID',poductID,'我是數量',quantity)
                fetch(`http://localhost/happypet/happypet_back/public/api/product/insert/${user}/${poductID}/${quantity}`,{
                // fetch(`http://localhost/happypet/happypet_back/public/api/product/insert/${poductID}/${quantity}`,{
                    method:'get'
                })
                .then(response=>response.text())
                .then(rows =>{
                    // console.log('插入資料',rows )
                    if(rows > 0 ){
                        setTimeout(() => {
                            addCartIcon.style.opacity = "0"
                            addCartCheckIcon.style.opacity = "1"
                        }, 1000);
                        setTimeout(() => {
                            addCartCheckIcon.style.opacity = "0"
                            addCartText.style.opacity = "1"
                        }, 2500);
                        queryQuantity(user)
                    }else{
                        $('#add_cart').html()
                    }
                })
            }
        }
        console.log('id',localStorage.getItem("uid"))
        if(localStorage.getItem("uid")){
            queryQuantity(localStorage.getItem("uid"))
        }else{
            localStorage.removeItem("uid")
            localStorage.removeItem("cartQuantity")
        }
        // 更新購物車數量(紅點圖標數量)
        // function queryQuantity(){
            // fetch('http://localhost/happypet/happypet_back/public/api/productcart/1')
        function queryQuantity(user){
            fetch(`http://localhost/happypet/happypet_back/public/api/productcart/${user}`)
            .then(response=>response.text())
            .then(quantity=>{
                console.log('購物車quantity',quantity)

                if(!quantity || quantity == 0){
                    // cartQuantity.style.display = 'none'
                    $('.nav_icon .cart_quantity').addClass('d-none');
                }else{
                    // cartQuantity.style.display = 'block'
                    $('.nav_icon .cart_quantity').removeClass('d-none');
                    $('.nav_icon .cart_quantity').text(quantity);
                    localStorage.setItem("cartQuantity", quantity);
                    console.log("購物車quantity localStorage",localStorage.getItem("cartQuantity"))
                    // cartQuantity.innerText = quantity
                }
            })
        }
        // console.log(localStorage.getItem("cartQuantity"))

        // 選擇按鈕後更新價格
        function checkInput(){
            let selectFlavorOrColor = document.querySelector('.flavorOrColorArea input[type="radio"]:checked')?.value
            let selectWeightOrSize = document.querySelector('.weightOrSizeArea input[type="radio"]:checked')?.value
            // let selectedProduct = products.find(product=>{
            let selectedProduct = products.find(product=>{
                // console.log('product[flavorOrColor]',product[flavorOrColor])
                // console.log('selectFlavorOrColor',selectFlavorOrColor)
                // console.log('product[weightOrSize]',product[weightOrSize])
                // console.log('selectWeightOrSize',selectWeightOrSize)
                // if(selectFlavorOrColor){
                    // console.log('product[flavorOrColor] === selectFlavorOrColor',product[flavorOrColor] === selectFlavorOrColor)
                // }
                // console.log('product[weightOrSize] === selectWeightOrSize', product[weightOrSize] === selectWeightOrSize)
                return product[flavorOrColor] === selectFlavorOrColor && product[weightOrSize] === selectWeightOrSize
            })
            console.log('selectFlavorOrColor',selectFlavorOrColor)
            console.log('selectWeightOrSize',selectWeightOrSize)
            
            productPrice.innerText = selectedProduct.price.toLocaleString()
            // console.log('xxxxxxxx',selectedProduct)
            productID = selectedProduct.product_id
            // console.log('productID',productID)
            $('#price').removeClass("d-none")

        }
        /**************************************************************************************************************/
        let allFlavorOrColorInputs = document.querySelectorAll('.flavorOrColorArea input[type="radio"]')
        allFlavorOrColorInputs.forEach(input => {
            input.addEventListener('input',findDisableInput)
        });
        let allWeightOrSizeInputs = document.querySelectorAll('.weightOrSizeArea input[type="radio"]') 
        allWeightOrSizeInputs.forEach(input => {
            input.addEventListener('input',findDisableInput)
        });
        /**************************************************************************************************************/

        // 使下架的產品的input框disabled，搭配一開始產品filter(剃除狀態為f-下架產品)
        function findDisableInput(){
            let selectFlavorOrColorValue = document.querySelector('.flavorOrColorArea input[type="radio"]:checked')?.value
            let selectWeightOrSizeValue = document.querySelector('.weightOrSizeArea input[type="radio"]:checked')?.value
            // if(!selectFlavorOrColorValue || !selectWeightOrSizeValue) return //如果沒被選擇就return

            let allFlavorOrColorInputs = document.querySelectorAll('.flavorOrColorArea input[type="radio"]')
            let allWeightOrSizeInputs = document.querySelectorAll('.weightOrSizeArea input[type="radio"]') 
            allFlavorOrColorInputs.forEach((input)=>{
                // 檢查是否有任意產品的 flavorOrColor 屬性值與該按鈕的值匹配，且其 weightOrSize 屬性值與當前選擇的重量或尺寸值 (selectedWeightOrSizeValue) 匹配。
                // input.disabled = !products.some((product)=>{ 
                let flag = true //預設所有選項禁用
                products.forEach((product)=>{ 
                    // return product[flavorOrColor] === input.value && product[weightOrSize] === selectWeightOrSizeValue 
                    // 資料庫寵物睡窩[水上樂園] === input選擇水上樂園 && (資料庫寵物睡窩[2XL找不到]!undefined === )
                    // true && 
                    // console.log(product[flavorOrColor],"===",input.value,'&& (',!selectWeightOrSizeValue,"||",product[weightOrSize],'=== ' ,selectWeightOrSizeValue,")")
                    // 資料庫寵物睡窩[水上樂園] === input選擇水上樂園 
                    if(product[flavorOrColor] === input.value){
                        // 且沒有被選擇的size || 資料庫寵物睡窩[XL] ==== 選擇的XL
                        if(!selectWeightOrSizeValue || product[weightOrSize] === selectWeightOrSizeValue){
                            flag = false
                        }
                    }
                    // console.log('!selectWeightOrSizeValue ',!selectWeightOrSizeValue,"||","product[weightOrSize] ",product[weightOrSize] ,'=== selectWeightOrSizeValue = ',selectWeightOrSizeValue)
                    // return product[flavorOrColor] === input.value && (!selectWeightOrSizeValue || product[weightOrSize] === selectWeightOrSizeValue) 
                })
                input.disabled = flag;
                // input.disabled = isDisabledStatus;
                // console.log('input.disabled',input.disabled)
            })
            allWeightOrSizeInputs.forEach((input)=>{
                let flag = true //預設所有選項禁用
                // 檢查是否有任意產品的 weightOrSize 屬性值與該按鈕的值匹配，且其 flavorOrColor 屬性值與當前選擇的口味或顏色值 (selectedFlavorOrColorValue) 匹配。
                // input.disabled = !products.some((product)=>{ 
                products.forEach((product)=>{ 
                    if(product[weightOrSize] === input.value){
                        if(!selectFlavorOrColorValue || product[flavorOrColor] === selectFlavorOrColorValue){
                            flag = false
                        }
                    }
                    // return product[weightOrSize] === input.value && product[flavorOrColor] === selectFlavorOrColorValue 
                    // return product[weightOrSize] === input.value && (!selectFlavorOrColorValue || product[flavorOrColor] === selectFlavorOrColorValue);
                })
                input.disabled = flag;
                // input.disabled = isDisabledStatus;
                // console.log('input.disabled',input.disabled)
            })
        }
        addCartBtn.onclick = ()=>{
            getProductInfo(localStorage.getItem("uid"),productID,currentQuantity ? currentQuantity : pdQuantity.value)
            queryQuantity(localStorage.getItem("uid"))
        }    
     
    })
    .catch(error => {
        console.error('Error:', error);
        
    });
    
}