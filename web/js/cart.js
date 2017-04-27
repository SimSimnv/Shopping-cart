$( document ).ready(function() {

    $('#app_bundle_cart_type_purchases input').on('input',calcPrice);

    function calcPrice() {

        let userMoney=$('.user-money').val()
        let totalPrice=0;

        let priceArr=[];
        for(let priceElement of $('.purchase-price').toArray()) {
            let price=parseFloat(priceElement.value);
            priceArr.push(price);
        }

        let quantityArr=[];
        for(let quantityElement of $('#app_bundle_cart_type_purchases input').toArray() ){
            let quantity=parseInt(quantityElement.value);
            quantity=quantity>1?quantity:1;
            quantityArr.push(quantity);
        }

        for (let i=0; i<priceArr.length; i++){
            let price=priceArr[i];
            let quantity=quantityArr[i];

            totalPrice+=price*quantity;
        }

        let cartTotal=$('.cart-total');
        cartTotal.text(totalPrice+'$');

        if(totalPrice>userMoney){
            cartTotal.css('color','red');
        }
        else{
            cartTotal.css('color','green');
        }

    }

    calcPrice();

});
