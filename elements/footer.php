<!-- footer -->
<footer class="footer">
    <div class="container-inner" style="position: relative; color: #CDCDCD;">
        <div class="socials">
            <a href="https://www.youtube.com/channel/UCsD72HATFzpKD0kJJPjF57A?sub_confirmation=1" target="_blank">
                <div class="icon yt"></div>
            </a>
            <a href="https://vk.com/fluidline" target="_blank">
                <div class="icon vk"></div>
            </a>
            <a target="_blank">
                <div class="icon ok"></div>
            </a>
            <a href="https://www.linkedin.com/company/fluidline/" target="_blank">
                <div class="icon in"></div>
            </a>
            <a href="https://t.me/fluidline_ru/" target="_blank">
                <div class="icon tlg"></div>
            </a>
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
                        <a href="tel:+74959844100" class="footer-phone-mobile" style="color: #999999; text-decoration:none;">
                            +7 (495) 984-41-00
                        </a>
                        <br>
                        <a href="mailto:mail@fluid-line.ru" style="color: #999999">
                            mail@fluid-line.ru
                        </a>
                        <br>
                        Москва, Большая Cеменовская ул., д.49
                        <br>
                        Большая Cеменовская ул., д.49
                        <br>

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

<script>
    new WOW().init();

    function openMailSender(option) {
        let MailSender = document.querySelector('.modal-container.' + option);
        MailSender.style.display = 'flex';
    }

    function showAlert() {
        document.querySelector('.modal-container.alert').style.display = "flex";
    }

    cookie.set("invoice_url", "<?= $_SERVER['REQUEST_URI'] ?>");

    $('#close-modal-position').on('click', function () {
        $(this).closest('.modal-container').hide();
        localStorage.setItem('positionClosed', 1);
    });

    if (!localStorage.getItem('positionClosed')){
        $('.modal-container.position').css({display: 'flex'});
    }

    $('form#position-form').on('submit', e => {
        e.preventDefault();

        const form = e.target;
        const data = validate(form);

        if (!data)
            return false;

        fetch('/api/position.php', {
            method: 'POST',
            body: JSON.stringify(data),

        }).then(function () {
            form.innerHTML = `<h2 style="font-size: 36px; font-weight:bold;">Спасибо за обратную связь!</h2>`;

            setTimeout(() => {
                $('.modal-container.position').hide();
            }, 3000);
        });
    });

    <?php if(isset($_GET['mailer'])) :?>
        openMailSender('<?= $_GET['mailer'] ?>');
    <?php endif;?>

</script>


</div>
</body>
</html>