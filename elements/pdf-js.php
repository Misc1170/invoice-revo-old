<script src="/invoice/pdf.min.js"></script>

<br>
<hr>
<br>

<div class="preview-area">
    <div id="previews"></div>

    <div id="fullsize">

        <span class="options" style="display: block; font-size: 22px;margin-top: 15px;">

            <?php if (preg_match('/pdf$/', trim($pdfPath))): ?>
            <a data-href="<?= $data_link ?>" href="<?= $fl_link ?>" target="_blank" class="open-link" title="Открыть">
                <? else: ?>
                <a style="opacity: .4">
                    <?php endif; ?>
                    <i class="fa fa-eye" aria-hidden="true"></i>
                    <span class="title">Открыть</span>
                </a>
                &nbsp;&nbsp;
                <a data-href="<?= $data_link ?>" href="<?= $fl_link ?>" target="_blank" download title="Скачать">
                    <i class="fa fa-download" aria-hidden="true"></i>
                    <span class="title">Скачать</span>
                </a>
                 &nbsp;&nbsp;
                <?php if (preg_match('/pdf$/', trim($pdfPath))): ?>
                <a href="/print" class="print-link" title="Распечатать">
                    <span class="loader">
                        <i class="fa fa-spinner fa-pulse"></i></span>

                    <? else: ?>
                    <a style="opacity: .4">
                        <?php endif; ?>
                        <i class="fa fa-print" aria-hidden="true"></i>
                        <span class="title">Печать</span>
                    </a>
                     &nbsp;&nbsp;
                    <a <?= $a_inner ?> title="Оплата">
                        <i class="fa fa-credit-card-alt <?= $pay_class ?>" aria-hidden="true"></i>
                        <span class="title">Оплата</span>
                    </a>

        </span>

        <canvas id="fullsize-canvas"></canvas>

    </div>
</div>


<div class="modal-container alert">
    <div class="modal-inner">
        <i class="fa fa-times fa-close-modal" aria-hidden="true"
           onclick="this.parentNode.parentNode.style.display='none';"></i>
        <?php if ($fetch['pay_link']): ?>
            <span><?= current(explode('.', $fileName)) ?></span> выставлен на юридическое лицо.
                                                                 Для оплаты от частного лица, введите пожалуйста ФИО плательщика.
            <form id="legal_entities_payment">
                <input type="text" placeholder="ФИО" name="fullname" class="form-control" required/>
                <button type="submit" class="btn btn-primary">Оплатить</button>
            </form>
            <span id="details">* при оплате кредитной картой отгрузочные документы будут выставлены на частное лицо. Выставить документы на юр. лицо будет невозможно</span>
        <?php else: ?>
            <p>Оплата картой невозможна. Просьба за подробной информацией обращаться к Вашему менеджеру.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal-container mailsender">
    <div class="modal-inner">
        <i class="fa fa-times fa-close-modal" aria-hidden="true"
           onclick="this.parentNode.parentNode.style.display='none';"></i>

        <form action="https://fluid-line.ru/invoice/mail-sender.php" method="get" onsubmit="ym(5484148,'reachGoal','invoicesendmail')">
            <input type="email" name="email" placeholder="Email, на который отправить письмо со счетом" class="form-control" style="height: 45px;" required>
            <input type="hidden" name="option" value="">
            <input type="hidden" name="id" value="<?= isset($fetch['id']) ? $fetch['id'] : ''; ?>">
            <input type="hidden" name="file" value="<?= $data_link ?>">
            <button class="btn btn-primary" type="submit">Отправить письмо</button>
            <hr style="margin: 20px 0 0;">
            <i style="font-size: 11px;">Если письмо долго не приходит, пожалуйста, проверьте папку "СПАМ"</i>
        </form>
    </div>
</div>

<div class="modal-container mailsender-accountant">
    <div class="modal-inner">
        <i class="fa fa-times fa-close-modal" aria-hidden="true"
           onclick="this.parentNode.parentNode.style.display='none';"></i>

        <form action="https://fluid-line.ru/invoice/mail-sender.php" method="get" onsubmit="ym(5484148,'reachGoal','invoicesendbuh')">
            <input type="email" name="email" placeholder="Email, на который отправить письмо со счетом" class="form-control" style="height: 45px;" required>
            <input type="hidden" name="id" value="<?= isset($fetch['id']) ? $fetch['id'] : ''; ?>">
            <input type="hidden" name="option" value="accountant">
            <input type="hidden" name="file" value="<?= $data_link ?>">
            <button class="btn btn-primary" type="submit">Отправить письмо</button>
            <hr style="margin: 20px 0 0;">
            <i style="font-size: 11px;">Если письмо долго не приходит, пожалуйста, проверьте папку "СПАМ"</i>
        </form>
    </div>
</div>

<div class="modal-container mailsender-director">
    <div class="modal-inner">
        <i class="fa fa-times fa-close-modal" aria-hidden="true"
           onclick="this.parentNode.parentNode.style.display='none';"></i>

        <form action="https://fluid-line.ru/invoice/mail-sender.php" method="get" onsubmit="ym(5484148,'reachGoal','invoicesendruk')">
            <input type="email" name="email" placeholder="Email, на который отправить письмо со счетом" class="form-control" style="height: 45px;" required>
            <input type="hidden" name="id" value="<?= isset($fetch['id']) ? $fetch['id'] : ''; ?>">
            <input type="hidden" name="option" value="director">
            <input type="hidden" name="file" value="<?= $data_link ?>">
            <button class="btn btn-primary" type="submit">Отправить письмо</button>
            <hr style="margin: 20px 0 0;">
            <i style="font-size: 11px;">Если письмо долго не приходит, пожалуйста, проверьте папку "СПАМ"</i>
        </form>
    </div>
</div>

<div class="modal-container position">
    <div class="modal-inner">
        <i class="fa fa-times fa-close-modal" aria-hidden="true" id="close-modal-position"></i>

        <form action="" id="position-form" class="validate-form">
            <p><b>Помогите нам сделать сайт лучше.</b></p>
            <input type="hidden" name="id" value="<?= $fetch['id'] ?>">
            <input type="text" name="position" placeholder="Расскажите, кто Вы по профессии?"
                   class="form-control" required>
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Отправить</button>
            <small id="position-result"></small>
        </form>
    </div>
</div>

<script src="/invoice/print.min.js"></script>

<script type="text/javascript">

    if (document.querySelector('.print-link') !== null) {
        $('.print-link').on('click', function (e) {
            e.preventDefault();
            const self = this;
            self.classList.add('loading');
            printJS('<?= 'https://fluid-line.ru/invoice/redirect.php?pdf_link=' . $pdfPath ?>');
            setTimeout(() => self.classList.remove('loading'), 1000);
        });
    }


    $('#legal_entities_payment').on('submit', function (e) {
        e.preventDefault();
        const json = {
            email: '<?= isset($fetch['email']) ? $fetch['email'] : '' ?>',
            fullname: this.elements['fullname'].value
        };
        const callback = response => {
            if (response.result)
                window.location.href = "<?= $linka ?>";
        };
        fetchfunc('/invoice/legal_entities_payment.php', callback, json);
    });
</script>

<?php if (preg_match('/pdf$/', trim($pdfPath))): ?>

    <script type="text/javascript">

        var pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = '/invoice/pdf.worker.min.js';

        pdfjsLib.getDocument('<?= 'https://fluid-line.ru/invoice/redirect.php?pdf_link=' . $pdfPath ?>').promise.then(doc => {
            let pages_count = doc._pdfInfo.numPages;

            //console.log('pages_count' + pages_count);

            for (let i = 1; i <= pages_count; i++) {
                doc.getPage(i).then(page => {
                    let canvas = document.createElement('canvas');

                    canvas.setAttribute('data-page', i);

                    canvas.onclick = function () {

                        let canvas = document.getElementById('fullsize-canvas');
                        canvas.parentNode.style.display = 'flex';
                        let context = canvas.getContext('2d');
                        let viewport = page.getViewport({scale: 2});

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        canvas.parentNode.onclick = function (e) {
                            if (!$(e.target).closest('span.options').length)
                                this.style.display = 'none';
                        };

                        page.render({
                            canvasContext: context,
                            viewport: viewport
                        });

                        //$( "span.options" ).clone().appendTo("#fullsize");
                    };

                    document.querySelector('.preview-area #previews').appendChild(canvas);
                    let context = canvas.getContext('2d');
                    let viewport = page.getViewport({scale: 1});

                    canvas.width = viewport.viewBox[2];
                    canvas.height = viewport.viewBox[3];

                    page.render({
                        canvasContext: context,
                        viewport: viewport
                    });
                });
            }

        });


        $('body').on('keydown', e => {
           //console.log(e.keyCode);
            if(e.keyCode === 80){
                e.preventDefault();
                printJS('<?= 'https://fluid-line.ru/invoice/redirect.php?pdf_link=' . $pdfPath ?>');
            }
        });
    </script>

<? endif; ?>