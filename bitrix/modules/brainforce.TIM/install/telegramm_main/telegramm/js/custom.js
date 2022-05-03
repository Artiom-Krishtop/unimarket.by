/*
You can use this file with your scripts.
It will not be overwritten when you upgrade solution.
*/
var lastTr = null;
$(document).ready(function(){
    $("body").on("change", "#setparams select[name=LIST_PRICE]", function(){
        var line = '';
        switch($(this).val()){
            case "Type":
                line = '<tr><td width="50%" class="adm-detail-content-cell-l">ID типа цены<a name="opt_ID_GROUP_CATALOG"></a></td><td width="50%" class="adm-detail-content-cell-r"><input type="text" size="20" maxlength="255" value="" name="ID_GROUP_CATALOG"></td></tr>';
                if(lastTr != null)
                    lastTr.next().remove();
                lastTr = $(this).closest('tr').after(line);
                break;
            case "Poperty":
                line = '<tr><td width="50%" class="adm-detail-content-cell-l">Введите символьный код сво-ва инфоблока для цены<a name="opt_CODE_PRICE_ELEMENT"></a></td><td width="50%" class="adm-detail-content-cell-r"><input type="text" size="20" maxlength="255" value="" name="CODE_PRICE_ELEMENT"></td></tr>';
                if(lastTr != null)
                    lastTr.next().remove();
                lastTr = $(this).closest('tr').after(line);
                break;
            default:
                break;
        };
    });


    $("#FormMessageTelegramm").on("submit", function(e){
        e.preventDefault();
        // console.log($(this));
        SendTelegramm(this);
        return false;
    });

    $("#EditMenuTelegramm").on("submit", function(e){
        e.preventDefault();
        // console.log($(this));
        SendTelegrammMenu(this);
        return false;
    });

    $("#soonSend").on("change", function(){
        ViewDateInput($(this));
        return false;
    });

    $("#command").on("change", function(){
        ViewTextCommand($(this));
        return false;
    });

    $(".AddNewLineButton").on("click", function(){
        AddNewLineButton();
    });

    $(document).on("click", ".AddNewLineButtonMenu", function(){
        let height = $(this).prev().children('select').data('number')+1;
        let line = '<tr><td><div class="LineButton"><input type="text" name="keyboard[]" placeholder="Введите текст кнопки"><input type="text" name="PositionY[]" style="display: none;" value="'+height+'"><input type="text" name="PositionX[]" style="display: none;" value="1"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'+height+'" data-width="1"><option value="none">Нет действий</option><option value="Catalog">Каталог</option><option value="about">О компании</option><option value="feedback">Обратная связь</option><option value="repost">Поделиться с другом</option></select><div class="AddNewButtonInLineMenu">+</div></div></td></tr>';
        $(this).closest('tr').after(line);
        if(height < 4){
            $(this).appendTo($(this).closest('tr').next().children('td'));
        } else {
            $(this).remove();
        }
        
    });

    $(document).on("click", ".AddNewButtonInLineMenu", function(){
        let width = $(this).prev().data("width")+1;
        let height = $(this).prev().data('number');
        let colAdd = '<div class="AddNewButtonInLineMenu">+</div>';
        if(width >= 3){
            colAdd = '';
        }
        let line = '<td><div class="LineButton"><input type="text" name="keyboard[]" placeholder="Введите текст кнопки"><input type="text" name="PositionY[]" style="display: none;" value="'+height+'"><input type="text" name="PositionX[]" style="display: none;" value="'+width+'"><select name="TextCommandButton[]" class="typeselect DoButton" data-number="'+height+'" data-width="'+width+'"><option value="none">Нет действий</option><option value="Catalog">Каталог</option><option value="about">О компании</option><option value="feedback">Обратная связь</option><option value="repost">Поделиться с другом</option></select>'+colAdd+'</div></td>';
        $(this).closest('td').after(line);
        $(this).remove();
    });

    $("body").on("change", ".DoButton", function(){
        var line = '';
        var NextElement = $(this).next();
        if(NextElement.hasClass('input_select')){
            NextElement.remove();
        }
        switch($(this).val()){
            case "NextMessage":
                line = '<div class="input_select" data-number="'+$(this).data('number')+'"><input type="text" name="DoButton[]" placeholder="Введите ID сообщения"></div>';
                $(this).after(line);
                break;
            case "OpenPage":
                line = '<div class="input_select" data-number="'+$(this).data('number')+'"><input type="text" name="DoButton[]" placeholder="Введите URL"></div>';
                $(this).after(line);
                break;
            default:
                $(".input_select[data-number='"+$(this).data('number')+"']").remove();
                break;
        }
    });
});

function AddNewLineButton(){
    let number = $(".LineButton").length+1;
    let line = '<div class="LineButton"><input type="text" name="keyboard[]" placeholder="Введите текст кнопки"><select name="TextCommandButton[]" class="DoButton" data-number="'+ number +'"><option value="none">Нет действий</option><option value="NextMessage">Ссылка на другое сообщение</option><option value="OpenPage">Ссылка на сайт</option></select></div>';
    $('.AddNewLineButton').before(line);
    if($(".LineButton").length == 5){
        $('.AddNewLineButton').remove();
    }
}

function ViewTextCommand(input){
    if (input.is(':checked')){
        $("#SetCommand").css('display', 'flex');
        $("#SetTimeSoonSend").css('display', 'none');
        $("#soonSend").prop('checked', false);
    } else {
        $("#SetCommand").css('display', 'none');
    }
}

function ViewDateInput(input){
    if (input.is(':checked')){
        $("#SetTimeSoonSend").css('display', 'flex');
        $("#SetCommand").css('display', 'none');
        $("#command").prop('checked', false);
    } else {
        $("#SetTimeSoonSend").css('display', 'none');
    }
}

function SendTelegramm(form){
    var fd = new FormData(form);
    $.ajax({
        url: "/telegramm/ajax/SendMessageSelectUser.php",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function(data){
            // console.log(data);
            let message = JSON.parse(data);
            $("#ResultSendTelegramm").empty();
            for(let i=0; i<message.length; i++){
                $("#ResultSendTelegramm").append(message[i]+"<br><br>");
            }
        }
    });
}

function SendTelegrammMenu(form){
    var fd = new FormData(form);
    $.ajax({
        url: "/telegramm/ajax/SaveMenuTelegram.php",
        method: "POST",
        data: fd,
        processData: false,
        contentType: false,
        success: function(data){
            // console.log(data); 
            let message = JSON.parse(data);
            $("#ResultSendTelegramm").empty();
            for(let i=0; i<message.length; i++){
                $("#ResultSendTelegramm").append(message[i]+"<br><br>");
            }
        }
    });
}