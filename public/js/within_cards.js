$(document).ready(function(){

    //обновление изображения пользователя
    function settingUpdateImg(){
        $('fieldset input[type=file]').change(function(e){
            var _this = $(this);
            var reader = new FileReader();
            reader.onload = function (e) {
                if(_this.next('.image-container').children('img').length < 1){
                    _this.next('.image-container').append('<img src="" alt="">');
                }
                _this.next('.image-container').children('img').attr('src', e.target.result);
            }
            reader.readAsDataURL( $(this).prop('files')[0] );
        });
    }
    settingUpdateImg();

    function array_unique( inputArr ) {
        var result = [];
        $.each(inputArr, function(i, el){
            if($.inArray(el, result) === -1) result.push('"'+el+'"');
        });

        return result;
    }
    
    function showActionData(){
        $('#tableActionList thead tr td:eq(2) div').hide();
        $('#tableActionList tbody tr').hide();
        var slug = $('#tableActionList select[name=card_actions_select] option:selected').attr('data-title');
        $('#tableActionList thead tr td:eq(2) div[data-action-type='+slug+']').show();
        $('#tableActionList tbody tr[data-action-type='+slug+']').show();
    }
    
    
    function getAllGroups(){
        $.get(
            '/admin/cards/get_card_groups',
            function(data){
                data = JSON.parse(data);
                var selectorOptions = '';
                for(var i=0; i<data.length; i++){
                    selectorOptions += '<option value="' + data[i]['id'] + '">' + data[i]['title'] + '</option>';
                }
                $('#tableActionList #groupOfCards').empty().append(selectorOptions);
                
            }
        );
    }
    
    function getAllCards(){
        $.get(
            '/admin/get_cards_selector',
            function(data){
                data = data.substr(27);
                data = data.substr(0, data.length-9);
                $('body select#singleCardSelector').empty().append(data);
            }
        );
    }

    function viewHiddenGroupSelectors(object){
        object.parents('td').children('.container-wrap:gt(0)').addClass('disactive');
        object.parents('td').children('.container-wrap[data-selector='+object.attr('data-selector')+'_type'+object.val()+']').removeClass('disactive');
    }
    
    function checkCardType(){
        switch($('select[name=cardType]').val()){
            case 'neutrall':
                $('#cardCanNotBeSavedByRace').show();
                $('#cardInRace').hide();
                break;
            case 'race':
                $('#cardCanNotBeSavedByRace').hide();
                $('#cardInRace').show();
                break;
            default:
                $('#cardCanNotBeSavedByRace').hide();
                $('#cardInRace').hide();
                break;
        }
    }
    
    checkCardType();
    
    viewHiddenGroupSelectors($('#tableActionList .container-wrap input[name=brotherhood_actionToGroupOrSame]:checked'));
    viewHiddenGroupSelectors($('#tableActionList .container-wrap input[name=support_actionToGroupOrAll]:checked'));
    viewHiddenGroupSelectors($('#tableActionList .container-wrap input[name=fear_actionToGroupOrAll]:checked'));
    viewHiddenGroupSelectors($('#tableActionList .container-wrap input[name=killer_recomendedTeamateForceAmount_OnOff]:checked'));
    viewHiddenGroupSelectors($('#tableActionList .container-wrap input[name=killer_killAllOrSingle]:checked'));
    
    showActionData();
    getAllGroups();
    getAllCards();

    //Изменение действия
    $('#tableActionList select[name=card_actions_select]').change(function(){
        showActionData();
    });
    
    //Добавление карты в группу
    $('input[name=addCardToGroup]').click(function(){
        var groupId = $(this).parent().parent().find('select[name=addCardToGroup]').val();
        var groupTitle = $(this).parent().parent().find('select[name=addCardToGroup]').children('option:selected').text();
        $(this).parent().parent().children('#cardCurrentGroups').append('<tr><td><a class="drop" href="#"></a></td><td>' + groupTitle + '</td><td style="display: none;">' + groupId + '</td></tr>');
    });
    //добавить группу в действие
    $('#tableActionList input[name=addGroup]').click(function(){
        var groupId = $(this).parent().children('#groupOfCards').val();
        var groupTitle = $(this).parent().children('#groupOfCards').children('option:selected').text();
        $(this).parent().children('.edition').append('<tr><td><a class="drop" href="#"></a></td><td>' + groupTitle + '</td><td style="display: none;">' + groupId + '</td></tr>');
    });
    //добавить карту в действие
    $('#tableActionList input[name=addCard]').click(function(){
        var cardId = $(this).parent().children('#singleCardSelector').val();
        var cardTitle = $(this).parent().children('#singleCardSelector').find('option:selected').text();
        $(this).parent().children('.edition').append('<tr><td><a class="drop" href="#"></a></td><td>' + cardTitle + '</td><td style="display: none;">' + cardId + '</td></tr>');
    });
    //добавить магию в действие
    $('#tableActionList input[name=addAbility]').click(function(){
        var cardId = $(this).parent().children('#group_of_abilities').val();
        var cardTitle = $(this).parent().children('#group_of_abilities').find('option:selected').text();
        $(this).parent().children('.edition').append('<tr><td><a class="drop" href="#"></a></td><td>' + cardTitle + '</td><td style="display: none;">' + cardId + '</td></tr>');
    });
    
    //Убрать елемент из таблицы edition
    $(document).on('click','a.drop',function(e){
        e.preventDefault();
        $(this).parent().parent().remove();
    });
    
    $('#tableActionList').on('change', 'input[name=brotherhood_actionToGroupOrSame], select[name=healer_typeOfCard], input[name=support_actionToGroupOrAll], select[name=summon_typeOfCard], input[name=fear_actionToGroupOrAll], input[name=killer_recomendedTeamateForceAmount_OnOff], input[name=killer_killAllOrSingle]', function(){
        viewHiddenGroupSelectors($(this));
    });
    
    //Описание - Тип Карты
    $('select[name=cardType]').change(function (){
        checkCardType();
    });
    
    
    
    //Фикс json-массива: убираем из строки последние два символа -> ", "
    function fixString(row){
        row = row.substr(0, row.length -2);
        return row;
    }

    //Функция создания целевых групп действия
    function checkGroupTable(object){

        //создание json массива
        var realActionRow = '[';
        //создание строки описания
        var displayActionRow = '';

        var checked = 0; //если ничего не выбрано
        //выборка из таблицы добавленых групп (1е поле - удалить, 2е - название группы, 3я - hidden-> id групп)
        object.parent().children('table.edition').children('tbody').children('tr').each(function(){
            realActionRow += '"' + $(this).children('td:eq(2)').text() + '", ';
            displayActionRow += $(this).children('td:eq(1)').text() + ', ';
            checked = 1;
        });

        if(1 == checked){
            realActionRow = fixString(realActionRow);
            displayActionRow = fixString(displayActionRow);
        }
        realActionRow += ']';
        realActionRow = array_unique(JSON.parse(realActionRow));
        realActionRow = '[' + realActionRow + ']';
        displayActionRow += ';<br>';

        return [realActionRow, displayActionRow];
    }

    //Функция создания целевых рас/рядов действия
    function setCheckboxesToJson(object){
        var realActionRow = '[';
        var displayActionRow = '';

        var checked = 0; //если ничего не выбрано
        object.each(function(){
            realActionRow += '"' + $(this).val() + '", ';
            displayActionRow += $(this).parent().text() + ', ';
            checked = 1;
        });

        if(1 == checked) {
            realActionRow = fixString(realActionRow);
            displayActionRow = fixString(displayActionRow);
        }
        realActionRow += ']';
        displayActionRow+= ';<br>';

        return [realActionRow, displayActionRow];
    }
    
    
    
    //Добавление Действия
    $('input[name=addMoreCardActions]').click(function(){
        //Узнаем мип действия карты
        var actionType = $('select[name=card_actions_select] option:selected').attr('data-title');
        var _this = $(this);
        //отображение описания действия для пользователя
        displayActionRow= '<ins>' + $('select[name=card_actions_select] option:selected').text() + '</ins>: <br>';
        //описания действия для заноса в БД
        realActionRow   = '{"action": "' + $('select[name=card_actions_select]').val() + '"';
        switch(actionType){
            //Тип действия - "Бессмертный"
            case 'bessmertnyj':
                //Выбор дествия возврата карты на поле, или в руку
                realActionRow += ', "deadless_backToDeck":"' + $('input[name=deadless_backToDeck]:checked').val() + '"}';
                displayActionRow += ' - Возвращается: ' + $('input[name=deadless_backToDeck]:checked').parent().text() + ';<br>';
            break;
            //Тип действия - "Боевое Братство"
            case 'boevoe_bratstvo':
                //Выбор действия на группу или на одинаковые карты
                if (0 == $('input[name=brotherhood_actionToGroupOrSame]:checked').val()) {

                    //если выбор пал на одинаковые,- в БД пишем 0
                    realActionRow += ', "brotherhood_actionToGroupOrSame": "0"';
                    displayActionRow += ' - Дейстует на одинаковые; <br>';

                } else {
                    //если выбор пал на группу,- пишем в БД id групп

                    //создание целевых групп действия array[0 - json-массив, 1- строка описания]
                    var temp = checkGroupTable($('select[name=brotherhood_grop]'));
                    realActionRow += ', "brotherhood_actionToGroupOrSame": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Значение умножения силы
                realActionRow += ', "brotherhood_strenghtMult": "' + $('input[name=brotherhood_strenghtMult]').val() + '"}';
                displayActionRow += ' - Умножает силу в ' + $('input[name=brotherhood_strenghtMult]').val() + ' раз;<br>';
            break;
            //Воодушевление
            case 'voodushevlenie':
                //Выбор рядов действия

                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=inspiration_ActionRow]:checked'));
                realActionRow += ', "inspiration_ActionRow": ' + temp[0];
                displayActionRow += ' - Дальность: ' + temp[1];

                //Значение силы
                realActionRow += ', "inspiration_multValue": "' + $('input[name=inspiration_multValue]').val() + '"';
                displayActionRow += ' - Умножает силу в: ' + $('input[name=inspiration_multValue]').val() + ' раза;<br>';
                //Игнорирует иммунитет
                realActionRow += ', "inspiration_ignoreImmunity": "' + $('input[name=inspiration_ignoreImmunity]:checked').val() + '"}';
                displayActionRow += ' - Игнорирует полный иммунитет: ' + $('input[name=inspiration_ignoreImmunity]:checked').parent().text() + ';<br>';
            break;
            //Иммунитет
            case 'immunitet':
                //Выбор типа иммунитета: 0-простой/1-полный
                realActionRow += ', "immumity_type": "' + $('input[name=immumity_type]:checked').val() + '"}';
                displayActionRow += ' - Тип иммунитета: ' + $('input[name=immumity_type]:checked').parent().text();
            break;
            //Лекарь
            case 'lekar':
                realActionRow += ', "healer_typeOfCard": "' + $('select[name=healer_typeOfCard]').val() + '"';
                displayActionRow += ' - Тип карты: ' + $('select[name=healer_typeOfCard] option:selected').text() + '<br>';
                
                switch($('select[name=healer_typeOfCard]').val()){
                    case '0':
                        var temp = checkGroupTable($('select[name=healer_type_singleCard]'));
                        realActionRow += ', "healer_type_singleCard": ' + temp[0];
                        displayActionRow += ' - Карты: ' +temp[1];
                    break;
                    case '1':
                        var temp = setCheckboxesToJson($('.container-wrap input[name=healer_type_actionRow]:checked'));
                        realActionRow += ', "healer_type_actionRow": ' + temp[0];
                        displayActionRow += ' - Карта относится к ряду: ' + temp[1];
                    break;
                    case '2':
                        realActionRow += ', "healer_type_cardType": "' + $('input[name=healer_type_cardType]:checked').val()+'"';
                        displayActionRow += $('input[name=healer_type_cardType]:checked').parent().text() + '<br>';
                    break;
                    case '3':
                        var temp = checkGroupTable($('select[name=healer_type_group]'));
                        realActionRow += ', "healer_type_group": ' + temp[0];
                        displayActionRow += ' - Группы: ' +temp[1];
                    break;
                }
                //Выбор карты
                realActionRow += ', "healer_cardChoise": "' + $('input[name=healer_cardChoise]:checked').val()+'"';
                displayActionRow += ' - Способ выбора: '+$('input[name=healer_cardChoise]:checked').parent().text() + '<br>';
                //Играть карту из колоды
                realActionRow += ', "healer_deckChoise": "' + $('input[name=healer_deckChoise]:checked').val()+'"';
                displayActionRow += ' - Играть карту из колоды: '+$('input[name=healer_deckChoise]:checked').parent().text() + '<br>';
                //Игнорирует полный иммунитет
                realActionRow += ', "healer_ignoreImmunity": "' + $('input[name=healer_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует полный иммунитет: '+$('input[name=healer_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //Неистовство
            case 'neistovstvo':
                //Условие "Противник относится к Расе"

                //создание целевых рас array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=fury_enemyRace]:checked'));
                realActionRow += ', "fury_enemyRace": ' + temp[0];
                displayActionRow += ' - Карты противника имеют расу - ' + temp[1];
                
                //Условие "У противника есть определенная группа карт"
                temp = checkGroupTable($('select[name=fury_group]'));

                realActionRow += ', "fury_group": ' + temp[0];
                displayActionRow += ' - Противник имеет карту из группы: ' + temp[1];

                //Условие "Противник имеет определенное количество воинов в ряду"
                realActionRow += ', "fury_enemyWarriorsCount" : "' + $('input[name=fury_enemyWarriorsCount]').val() + '"';
                displayActionRow += ' - Противник имеет воинов в количестве: ' + $('input[name=fury_enemyWarriorsCount]').val() + ' в ряду: ';

                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                temp = setCheckboxesToJson($('td .container-wrap input[name=fury_ActionRow]:checked'));
                realActionRow += ', "fury_ActionRow": ' + temp[0];
                displayActionRow += temp[1];

                //Количество очков силы
                realActionRow += ', "fury_strenghtVal": "' + $('input[name=fury_strenghtVal]').val() + '"';
                displayActionRow += ' - Повышает силу на ' + $('input[name=fury_strenghtVal]').val() + ' единиц<br>';

                //Противник использовал способность
                realActionRow += ', "fury_abilityCastEnemy": ' +  $('input[name=fury_abilityCastEnemy]:checked').val();
                displayActionRow += ' - Противник использовал способность: ' + $('input[name=fury_abilityCastEnemy]:checked').parent().text();

                realActionRow += '}';
            break;
            //Одурманивание
            case 'odurmanivanie':
                //Условие Действует на ряд

                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=obscure_ActionRow]:checked'));
                realActionRow += ', "obscure_ActionRow": ' + temp[0];
                displayActionRow += " - Действует на ряд: " + temp[1];

                //Условие "Максимальная сила карты которую можно перетянуть"
                realActionRow += ', "obscure_maxCardStrength": "' + $('input[name=obscure_maxCardStrength]').val() + '"';
                displayActionRow += ' - Максимальная сила карты которую можно перетянуть: ' + $('input[name=obscure_maxCardStrength]').val() + ';<br>';

                //Условие степени силы перетягиваемой карты
                realActionRow += ', "obscure_strenghtOfCard": "' + $('select[name=obscure_strenghtOfCard]').val() + '"';
                displayActionRow += ' - Сила перетягиваемой карты: ' + $('select[name=obscure_strenghtOfCard] option:selected').text() + ';<br>';

                //Количество перетягиваемых карт
                realActionRow += ', "obscure_quantityOfCardToObscure": "' + $('input[name=obscure_quantityOfCardToObscure]').val() + '"';
                displayActionRow += ' - Количество перетягиваемых карт: ' + $('input[name=obscure_quantityOfCardToObscure]').val() + ';<br>';

                //Игнорирует полный иммунитет
                realActionRow += ', "obscure_ignoreImmunity": "' + $('input[name=obscure_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует полный иммунитет: '+$('input[name=obscure_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //перегрупировка
            case 'peregruppirovka':
                realActionRow += ', "regroup_ignoreImmunity": "' + $('input[name=regroup_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует полный иммунитет: '+$('input[name=regroup_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //Печаль
            case 'pechal':
                //Условие "Действует на ряд"
                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=sorrow_ActionRow]:checked'));
                realActionRow += ', "sorrow_ActionRow": ' + temp[0];
                displayActionRow += " - Действует на ряд: " + temp[1];
                realActionRow += ', "sorrow_actionTeamate": "' + $('input[name=sorrow_actionTeamate]:checked').val()+'"}';
                displayActionRow += ' - Действует на своих: '+$('input[name=sorrow_actionTeamate]:checked').parent().text() + '<br>';
            break;
            //Повелитель
            case 'povelitel':
                //Условие "Группа карт, которые будут призываться"
                var temp = checkGroupTable($('select[name=master_group]'));
                realActionRow += ', "master_group": ' + temp[0];
                displayActionRow += ' - Группа карт, которые будут призываться: ' + temp[1];

                //Условие "Откуда брать карты"
                temp = setCheckboxesToJson($('.container-wrap input[name=master_cardSource]:checked'));
                realActionRow += ', "master_cardSource": ' + temp[0];
                displayActionRow += ' - Карты берутся из: ' + temp[1];

                //Условие "Призывать карту по модификатору силы"
                realActionRow += ', "master_summonByModificator": "' + $('select[name=master_summonByModificator]').val() + '"';
                displayActionRow += ' - Призывать карту: ' + $('select[name=master_summonByModificator] option:selected').text() + ';<br>';

                //Условие "Максимальное количество карт, которое призывается"
                realActionRow += ', "master_maxCardsSummon": "' + $('input[name=master_maxCardsSummon]').val() + '"';
                displayActionRow += ' - Макс. количество карт, которое призывается: ' + $('input[name=master_maxCardsSummon]').val() + ';<br>';

                //Условие "Максимальное значение силы карт, которые призываются"
                realActionRow += ', "master_maxCardsStrenght": "' + $('input[name=master_maxCardsStrenght]').val() + '"';
                displayActionRow += ' - Макс. значение силы карт, которые призываются: ' + $('input[name=master_maxCardsStrenght]').val() + ';<br>';

                realActionRow += '}';
            break;
            //Поддержка
            case 'podderzhka':
                //Умение "Повысить силу"
                //создание целевых рядов array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=support_ActionRow]:checked'));
                realActionRow += ', "support_ActionRow": ' + temp[0];
                displayActionRow += ' - Повысить силу в ряду: ' + temp[1];

                if (0 == $('input[name=support_actionToGroupOrAll]:checked').val()) {
                    //если выбор пал на всех,- в БД пишем 0
                    realActionRow += ', "support_actionToGroupOrAll": "0"';
                    displayActionRow += ' - Дейстует на всех; <br>';
                } else {
                    //если выбор пал на группу,- пишем в БД id групп
                    var temp = checkGroupTable($('select[name=support_group]'));
                    realActionRow += ', "support_actionToGroupOrAll": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Условие "Повышение силы действует на себя"
                realActionRow += ', "support_selfCast": "' + $('input[name=support_selfCast]:checked').val() + '"';
                displayActionRow += ' - Повышение силы действует на себя: ' + $('input[name=support_selfCast]:checked').parent('label').text() + ';<br>';

                //Значение "Значение повышения силы"
                realActionRow += ', "support_strenghtValue": "' + $('input[name=support_strenghtValue]').val() + '"';
                displayActionRow += ' - Значение повышения силы на: ' + $('input[name=support_strenghtValue]').val() + ' единиц;<br>';

                //Условие "Игнорировать полный иммунитет"
                realActionRow += ', "support_ignoreImmunity": "' + $('input[name=support_ignoreImmunity]:checked').val() + '"}';
                displayActionRow += ' - Игнорировать полный иммунитет: ' + $('input[name=support_ignoreImmunity]:checked').parent('label').text() + ';<br>';
            break;
            //Подсмотреть карты
            case 'podsmotret_karty':
                realActionRow += ', "overview_cardCount": "' + $('input[name=overview_cardCount]').val()+'"}';
                displayActionRow += ' - Количество карт: ' +$('input[name=overview_cardCount]').val()+';<br>';
            break;
            //Призыв
            case 'prizyv':
                realActionRow += ', "summon_typeOfCard": "' + $('select[name=summon_typeOfCard]').val() + '"';
                displayActionRow += ' - Тип карты: ' + $('select[name=summon_typeOfCard] option:selected').text() + '<br>';
                
                switch($('select[name=summon_typeOfCard]').val()){
                    case '0':
                        var temp = checkGroupTable($('select[name=summon_type_singleCard]'));
                        realActionRow += ', "summon_type_singleCard": ' + temp[0];
                        displayActionRow += ' - Карты: ' +temp[1];
                    break;
                    case '1':
                        var temp = setCheckboxesToJson($('.container-wrap input[name=summon_type_actionRow]:checked'));
                        realActionRow += ', "summon_type_actionRow": ' + temp[0];
                        displayActionRow += ' - Карта относится к ряду: ' + temp[1];
                    break;
                    case '2':
                        realActionRow += ', "summon_type_cardType": "' + $('input[name=summon_type_cardType]:checked').val()+'"';
                        displayActionRow += $('input[name=summon_type_cardType]:checked').parent().text() + '<br>';
                    break;
                    case '3':
                        var temp = checkGroupTable($('select[name=summon_type_group]'));
                        realActionRow += ', "summon_type_group": ' + temp[0];
                        displayActionRow += ' - Группы: ' +temp[1];
                    break;
                }
                //Выбор карты
                realActionRow += ', "summon_cardChoise": "' + $('input[name=summon_cardChoise]:checked').val()+'"';
                displayActionRow += ' - Способ выбора: '+$('input[name=summon_cardChoise]:checked').parent().text() + '<br>';
                //Играть карту из колоды
                realActionRow += ', "summon_deckChoise": "' + $('input[name=summon_deckChoise]:checked').val()+'"';
                displayActionRow += ' - Играть карту из колоды: '+$('input[name=summon_deckChoise]:checked').parent().text() + '<br>';
                //Игнорирует полный иммунитет
                realActionRow += ', "summon_ignoreImmunity": "' + $('input[name=summon_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует полный иммунитет: '+$('input[name=summon_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //Сброс карт и поднятие из колоды
            case 'sbros_kart_i_podnyatie_iz_kolody':
                //Сколько карт сбросить
                realActionRow += ', "dropAndPick_dropCount": "'+ $('input[name=dropAndPick_dropCount]').val() + '"';
                displayActionRow += ' - Сколько карт сбросить: '+ $('input[name=dropAndPick_dropCount]').val()+'<br>';
                //Сколько взять с колоды
                realActionRow += ', "dropAndPick_pickCount": "'+ $('input[name=dropAndPick_pickCount]').val() + '"';
                displayActionRow += ' - Сколько карт поднять: '+ $('input[name=dropAndPick_pickCount]').val()+'<br>';
                //Выбор карты
                realActionRow += ', "dropAndPick_cardChoise": "'+ $('input[name=dropAndPick_cardChoise]:checked').val() + '"';
                displayActionRow += ' - Способ выбора карты: '+$('input[name=dropAndPick_cardChoise]:checked').parent().text() + '<br>';
                //Игнорирует полный иммунитет
                realActionRow += ', "dropAndPick_ignoreImmunity": "' + $('input[name=dropAndPick_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует полный иммунитет: '+$('input[name=dropAndPick_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //Сброс карт противника в отбой
            case 'sbros_kart_protivnika_v_otboj':
                //Количество карт
                realActionRow += ', "enemyDropHand_cardCount": "'+ $('input[name=enemyDropHand_cardCount]').val() + '"}';
                displayActionRow += ' - Количество карт: '+ $('input[name=enemyDropHand_cardCount]').val()+'<br>';
            break;
            //Страшный
            case 'strashnyj':
                //Раса на которую действует страх
                //создание целевых рас array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('td .container-wrap input[name=fear_enemyRace]:checked'));
                realActionRow += ', "fear_enemyRace": ' + temp[0];
                displayActionRow += ' - Не действует на расу: ' + temp[1];

                if (0 == $('input[name=fear_actionToGroupOrAll]:checked').val()) {
                    //Действует на всех
                    realActionRow += ', "fear_actionToGroupOrAll": "0"';
                    displayActionRow += ' - Дейстует на всех; <br>';

                } else {
                    //если выбор пал на группу,- пишем в БД id групп
                    var temp = checkGroupTable($('select[name=fear_group]'));
                    realActionRow += ', "fear_actionToGroupOrAll": ' + temp[0];
                    displayActionRow += ' - Действует на группу: ' + temp[1];
                }

                //Ряд действия
                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=fear_ActionRow]:checked'));
                realActionRow += ', "fear_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];

                //Условие "Действует на своих"
                realActionRow += ', "fear_actionTeamate": "' + $('input[name=fear_actionTeamate]:checked').val() + '"';
                displayActionRow += ' - Действует на своих: ' + $('input[name=fear_actionTeamate]:checked').parent('label').text() + ';<br>';

                //Значение понижения силы
                realActionRow += ', "fear_strenghtValue": "' + $('input[name=fear_strenghtValue]').val() + '"';
                displayActionRow += ' - Значение понижения силы: ' + $('input[name=fear_strenghtValue]').val() + ';<br>';

                //Игнорирует иммунитет
                realActionRow += ', "fear_ignoreImmunity": "' + $('input[name=fear_ignoreImmunity]:checked').val()+'"}';
                displayActionRow += ' - Игнорирует иммунитет: '+$('input[name=fear_ignoreImmunity]:checked').parent().text() + '<br>';
            break;
            //Убийца
            case 'ubijtsa':
                //Ряд действия
                //создание целевых рядов действия array[0 - json-массив, 1- строка описания]
                var temp = setCheckboxesToJson($('.container-wrap input[name=killer_ActionRow]:checked'));
                realActionRow += ', "killer_ActionRow": ' + temp[0];
                displayActionRow += ' - Ряд действия: ' + temp[1];
                //Условие "Действует на своих"
                realActionRow += ', "killer_atackTeamate": "' + $('input[name=killer_atackTeamate]:checked').val() + '"';
                displayActionRow += ' - Действует на своих: ' + $('input[name=killer_atackTeamate]:checked').parent().text() + ';<br>';
                //Условие "Выбрать для убийства карту"
                realActionRow += ', "killer_killedQuality_Selector": "' + $('select[name=killer_killedQuality_Selector]').val() + '"';
                displayActionRow += ' - Выбрать для убийства карту: ' + $('select[name=killer_killedQuality_Selector] option:selected').text()+'<br>';
                //Условие "Нужное для совершения убийства количество силы карт воинов в ряду"
                if (0 == $('input[name=killer_recomendedTeamateForceAmount_OnOff]:checked').val()) {
                    realActionRow += ', "killer_recomendedTeamateForceAmount_OnOff": "0"';
                }else{
                    realActionRow += ', "killer_recomendedTeamateForceAmount_OnOff": "' + $('input[name=killer_recomendedTeamateForceAmount]').val() + '"';
                    displayActionRow += ' - Количество силы необходимое для совершения убийства воинов: ' + $('input[name=killer_recomendedTeamateForceAmount]').val();
                    
                    var temp = setCheckboxesToJson($('.container-wrap input[name=killer_recomendedTeamateForceAmount_ActionRow]:checked'));
                    realActionRow += ', "killer_recomendedTeamateForceAmount_ActionRow": ' + temp[0];
                    displayActionRow += ' -> Ряд подсчета: ' + temp[1];
                    
                    realActionRow += ', "killer_recomendedTeamateForceAmount_Selector": "' + $('select[name=killer_recomendedTeamateForceAmount_Selector]').val() + '"';
                    displayActionRow += '(' + $('select[name=killer_recomendedTeamateForceAmount_Selector] option:selected').text() + ')<br>';
                }
                //Условие "Порог силы воинов противника для совершения убийства"
                realActionRow += ', "killer_enemyStrenghtLimitToKill": "' + $('input[name=killer_enemyStrenghtLimitToKill]').val() + '"';
                displayActionRow += ' - Порог силы воинов противника для совершения убийства: ' + $('input[name=killer_enemyStrenghtLimitToKill]').val() + ';<br>';
                realActionRow += ', "killer_killAllOrSingle": "'+$('input[name=killer_killAllOrSingle]:checked').val()+'"';
                displayActionRow += ' - На кого действует карта: '+$('input[name=killer_killAllOrSingle]:checked').parent().text()+ ';<br>';
               
                var temp = checkGroupTable($('select[name=killer_group]'));
                realActionRow += ', "killer_group": ' + temp[0];
                displayActionRow += ' - Действует на группу: ' + temp[1];
                
                //Условие "Игнорирует иммунитет"
                realActionRow += ', "killer_ignoreKillImmunity": "' + $('input[name=killer_ignoreKillImmunity]:checked').val() + '"}';
                displayActionRow += ' - Игнорирует иммунитет: ' + $('input[name=killer_ignoreKillImmunity]:checked').parent().text() + ';<br>';
            break;
            //Шпион
            case 'shpion':
                //Поле игрока
                realActionRow += ', "spy_fieldChoise": "'+$('input[name=spy_fieldChoise]:checked').val()+'"';
                displayActionRow += ' - Поле игрока: '+$('input[name=spy_fieldChoise]:checked').parent().text()+ ';<br>';
                //Плучить из колоды n карт
                realActionRow += ', "spy_getCardsCount": "' + $('input[name=spy_getCardsCount]').val() + '"';
                displayActionRow += ' - Плучить из колоды ' + $('input[name=spy_getCardsCount]').val() + ' карт';

                realActionRow += '}';
            break;
            //Остальные действия
            default: realActionRow += '}';
        }
        
        actionsPreviewTable(_this, displayActionRow, realActionRow);
    });
    
    //Добавление действия в Карту
    function actionsPreviewTable(object, display, actions){
        object.parent().parent().children('#cardCurrentActions').append('<tr><td><a class="drop" href="#"></a></td><td>' + display + '</td><td style="display: none;">' + actions + '</td></tr>');
        $('#cardCurrentActions tr td').on('click', 'a.drop', function(e){
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }
    
    //ДОБАВЛЕНИЕ КАРТЫ
    $('input[name=cardAdd]').click(function(){
        var token = $('input[name=_token]').val();

        var card_refer_to_group = [];  // Карта относится к группе
        $('#cardCurrentGroups tr').each(function(){
            card_refer_to_group.push( $(this).children('td:eq(2)').text() );
        });

        var card_actions = []; //Действия карты
        $('#cardCurrentActions tr').each(function(){
            card_actions.push( $(this).children('td:eq(2)').text() );
        });

        var card_type = $('select[name=cardType]').val(); // Тип карты

        var card_type_forbidden_race_deck = []; // Карта не может быть сыграна в колоде расы (Только для нейтралов)
        if(card_type == 'neutrall'){
            $('#cardCanNotBeSavedByRace td .container-wrap').each(function(){
                if($(this).children('input').prop('checked') === true){
                    card_type_forbidden_race_deck.push('"' + $(this).children('input').val() + '"');
                }
            });
        }

        var card_action_row = []; //Дальность карты
        $('.actions .container-wrap input[name=C_ActionRow]:checked').each(function(){
            card_action_row.push($(this).val());
        });
        
        var card_short_descr = tinyMCE.get('card_short_descr').getContent();
        var card_full_descr = tinyMCE.get('card_full_descr').getContent();


        //Создание иммитации формы
        var formData = new FormData();
        formData.append( 'token', token );
        formData.append( 'title', $('input[name=card_title]').val().trim() );               // Название карты
        formData.append( 'short_descr', card_short_descr );// Короткое описание
        formData.append( 'full_descr', card_full_descr );  // Полное описание
        formData.append( 'img_url', $('input[name=cardAddImg]').prop('files')[0] );         //Фон карты
        formData.append( 'card_refer_to_group', '[' + card_refer_to_group + ']');           // Json-массив "Карта относится к группам"
        formData.append( 'card_actions', '[' + card_actions + ']');                         // Json-массив "Действий карты"
        formData.append( 'card_type', card_type);                                           // Тип карты (нейтральная, спец.карта, расовая)
        formData.append( 'card_type_forbidden_race_deck', '[' + card_type_forbidden_race_deck + ']'); // Json-массив. Если карта нейтральная, указывается расса, которой данная карта не играется
        formData.append( 'card_race', $('#cardInRace select[name=cardRace]').val());        // Указывается расса в которой данная карта находится
        formData.append( 'card_action_row', '[' + card_action_row + ']');                   // Json-массив "дальность карты"
        formData.append( 'card_strenght', $('input[name=cardStrongthValue]').val());        //Сила карты
        formData.append( 'card_weight', $('input[name=cardWeightValue]').val());            //Вес карты
        formData.append( 'card_is_leader', $('input[name=cardIsLeader]').prop('checked'));  //Карта лидер?
        formData.append( 'card_max_num_in_deck', $('input[name=cardMaxValueInDeck]').val());//Максимальное число в колоде
        formData.append( 'card_gold_price', $('input[name=cardPriceGold]').val());          //Цена в золоте
        formData.append( 'card_silver_price', $('input[name=cardPriceSilver]').val());      //Цена в серебре

        $.ajax({
            url:        '/admin/cards/add',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            datatype:   'JSON',
            data:       formData,
            success:    function(data){
                if(card_type == 'race'){
                    var get = $('#cardInRace select[name=cardRace]').val();
                }else{
                    var get = card_type;
                }
                if(data == 'success') location = '/admin/cards?race='+get;
            }
        });
    });
    
    
    //Редактирование Карты
    $('input[name=cardEdit]').click(function(){

        var token = $('input[name=_token]').val();

        var card_refer_to_group = [];  // Карта относится к группе
        $('#cardCurrentGroups tr').each(function(){
            card_refer_to_group.push( $(this).children('td:eq(2)').text() );
        });

        var card_actions = []; //Действия карты
        $('#cardCurrentActions tr').each(function(){
            card_actions.push( $(this).children('td:eq(2)').text() );
        });

        var card_type = $('select[name=cardType]').val(); // Тип карты
        var card_type_forbidden_race_deck = []; // Карта не может быть сыграна в колоде расы (Только для нейтралов)
        if(card_type == 'neutrall'){
            $('#cardCanNotBeSavedByRace td .container-wrap').each(function(){
                if($(this).children('input').prop('checked') === true){
                    card_type_forbidden_race_deck.push('"' + $(this).children('input').val() + '"');
                }
            });
        }

        var card_action_row = []; //Дальность карты
        $('.actions .container-wrap input[name=C_ActionRow]:checked').each(function(){
            card_action_row.push($(this).val());
        });

        var card_short_descr = tinyMCE.get('card_short_descr').getContent();
        var card_full_descr = tinyMCE.get('card_full_descr').getContent();

        //Создание иммитации формы
        var formData = new FormData();
        formData.append( 'token', token );
        formData.append( 'id', $('input[name=card_id]').val() );
        formData.append( '_method', 'PUT');
        formData.append( 'title', $('input[name=card_title]').val().trim() );               // Название карты
        formData.append( 'short_descr', card_short_descr );// Короткое описание
        formData.append( 'full_descr', card_full_descr );  // Полное описание
        formData.append( 'img_url', $('input[name=cardAddImg]').prop('files')[0] );         //Фон карты
        formData.append( 'card_refer_to_group', '[' + card_refer_to_group + ']');           // Json-массив "Карта относится к группам"
        formData.append( 'card_actions', '[' + card_actions + ']');                         // Json-массив "Действий карты"
        formData.append( 'card_type', card_type);                                           // Тип карты (нейтральная, спец.карта, расовая)
        formData.append( 'card_type_forbidden_race_deck', '[' + card_type_forbidden_race_deck + ']'); // Json-массив. Если карта нейтральная, указывается расса, которой данная карта не играется
        formData.append( 'card_race', $('#cardInRace select[name=cardRace]').val());        // Указывается расса в которой данная карта находится
        formData.append( 'card_action_row', '[' + card_action_row + ']');                   // Json-массив "дальность карты"
        formData.append( 'card_strenght', $('input[name=cardStrongthValue]').val());        //Сила карты
        formData.append( 'card_weight', $('input[name=cardWeightValue]').val());            //Вес карты
        formData.append( 'card_is_leader', $('input[name=cardIsLeader]').prop('checked'));  //Карта лидер?
        formData.append( 'card_max_num_in_deck', $('input[name=cardMaxValueInDeck]').val());//Максимальное число в колоде
        formData.append( 'card_gold_price', $('input[name=cardPriceGold]').val());          //Цена в золоте
        formData.append( 'card_silver_price', $('input[name=cardPriceSilver]').val());      //Цена в серебре

        $.ajax({
            url:        '/admin/card/edit',
            headers:    {'X-CSRF-TOKEN': token},
            type:       'POST',
            processData: false,
            contentType: false,
            data:       formData,
            success:    function(data){
                if(card_type == 'race'){
                    var get = $('#cardInRace select[name=cardRace]').val();
                }else{
                    var get = card_type;
                }

                if(data == 'success') location = '/admin/cards?race='+get;
            }
        });
    });

});