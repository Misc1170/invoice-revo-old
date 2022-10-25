<!-- footer -->
<footer class="footer">
    <div class="container-inner" style="position: relative; color: #CDCDCD;">
        <div class="socials">
            <a href="https://www.youtube.com/channel/UCsD72HATFzpKD0kJJPjF57A?sub_confirmation=1" target="_blank"><div class="icon yt"></div></a>
            <a href="https://vk.com/fluidline" target="_blank"><div class="icon vk"></div></a>

            <a target="_blank"><div class="icon ok"></div></a>
            <a href="https://www.linkedin.com/company/fluidline/" target="_blank"><div class="icon in"></div></a>
            <a href="https://t.me/fluidline_ru/" target="_blank"><div class="icon tlg"></div></a>

            <!--a href="https://facebook.com/fluidline.ru" target="_blank"><div class="icon fb"></div></a>
            <a href="https://www.instagram.com/fluidline_ru/" target="_blank"><div class="icon inst"></div></a-->
        </div>
        <div style="position: absolute; left: 115px; top: 20px; font-size: 15px; color: #CBCBCB;">© <?= date('Y') ?> ООО «Флюид-Лайн» </div>
        <div>
            <table style="position: absolute; right:0; top: 20px;">
                <tbody><tr>
                    <td><a href="" style="color: #cccccc; font-size: 16px; font-family: arial;">Контакты</a><br><br></td>
                    <td></td>
                </tr>
                <tr style="font-size: 11px; color: #999999; font-family: tahoma;">
                    <td>
                        Телефон:<br>
                        Email:<br>
                        Адрес:<br>
                        Отгрузка<br>


                    </td>
                    <td>
                        <a href="tel:+74959844100" class="footer-phone-mobile" style="color: #999999; text-decoration:none;">+7 (495) 984-41-00</a><!--, <a href="tel:+74955177261" style="color: #999999;text-decoration:none;">(495) 517-7261</a>, <a href="tel:+74955170261" style="color: #999999;text-decoration:none;">(495) 517-0261</a>--><br>
                        <a href="mailto:mail@fluid-line.ru" style="color: #999999">mail@fluid-line.ru</a><br>
                        Москва, Большая Cеменовская ул., д.49<br>
                        Большая Cеменовская ул., д.49<br>

                    </td>
                </tr>
                </tbody></table>
            <address style="display:none" itemscope="" itemtype="http://schema.org/Organization">
                <span itemprop="name">ООО "Флюид-лайн"</span>
                <span itemprop="telephone">+7 (945) 984-41-00</span>
                <span itemprop="email">mail@fluid-line.ru</span>
                <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">
                    <span itemprop="addressLocality">Москва</span>
                    <span itemprop="streetAddress">Большая Семеновская улица, дом 49</span>
                </span>
            </address>

        </div>
    </div>
</footer>

<div class="vidPut" style="display: none;"></div>

<script src="/assets/vendor/jquery/jquery.jrumble.1.3.min.js"></script>

<!--Библиотека функций JS-->
<script src="/assets/js/lib.js"></script>

<script src="/invoice/wow-animation/wow.min.js"></script>
<script>
    new WOW().init();
</script>
<script>

    function openMailSender(option) {
        let MailSender = document.querySelector('.modal-container.' + option);
        MailSender.style.display = 'flex';
        //let form = MailSender.querySelector('form');
        /*let onsubmit = '';
        switch (option) {
            case "accountant":
                onsubmit = "ym(5484148,'reachGoal','invoicesendbuh')";
                break;
            case "director":
                onsubmit = "ym(5484148,'reachGoal','invoicesendruk')";
                break;
            default:
                onsubmit = "ym(5484148,'reachGoal','invoicesendmail')";
        }*/
        //form.setAttribute('onsubmit', onsubmit);
        //MailSender.querySelector('input[name="option"]').value = option;
    }

    function showAlert() {
        document.querySelector('.modal-container.alert').style.display = "flex";
    }

    (function interval() {
        setTimeout(function () {

            let data = {
                id:<?=$fetch['id']?>
            };

            fetch('https://fluid-line.ru/invoice/lastAction.php', {
                method: 'POST',
                body: JSON.stringify(data),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    console.log(data);
                });

            interval();
        }, 5 * 60 * 1000);
    })();
</script>
<script>
    $('a[download]').on('click', function (e) {
        e.preventDefault();
        const a = $(e.target).closest('a')[0];
        window.location.href = 'https://fluid-line.ru/invoice/download.php?link=' + a.dataset.href + '&filename=' + $('a.open-link').text();
    });


    $('a.open-link').on('click', function (e) {
        e.preventDefault();
        window.open('https://fluid-line.ru/invoice/invoice.php?pdf_link=' + $(e.target).closest('a').attr('data-href'));
    });


    cookie.set("invoice_url", "<?= $_SERVER['REQUEST_URI'] ?>");

    $('#close-modal-position').on('click', function () {
        $(this).closest('.modal-container').hide();
        localStorage.setItem('positionClosed', 1);
    });

    if (!localStorage.getItem('positionClosed'))
        $('.modal-container.position').css({display: 'flex'});

    $('form#position-form').on('submit', e => {
        e.preventDefault();
        const form = e.target;

        const data = validate(form);

        if (!data)
            return false;

        fetch('https://fluid-line.ru/invoice/position.php', {
            method: 'POST',
            body: JSON.stringify(data),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                console.log(response);

                if (response.result)
                    form.innerHTML = `<h2 style="font-size: 36px; font-weight:bold;">Спасибо за обратную связь</h2>`;
                else
                    form.innerHTML = `<h3 style="font-size: 30px;">Ошибка! Повторите позднее</h3>`;

                setTimeout(() => {
                    $('.modal-container.position').hide();
                }, 3000);
            });


    });

    <?php if (isset($_GET['print'])) :?>
        printJS('<?= 'https://fluid-line.ru/invoice/redirect.php?pdf_link=' . $pdfPath ?>');
    <?php elseif (isset($_GET['mailer'])) :?>
        openMailSender('<?= $_GET['mailer'] ?>');
    <?php endif;?>

</script>


</div>
</body>
</html>