<style>
    html {
        scrollbar-width: thin;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar {
        height: 10px;
        width: 10px;

        -webkit-appearance: none;
    }

    html::-webkit-scrollbar-thumb {
        background-color: rgba(47, 46, 46, 0.2);
        border: 3px solid #fbf6f6;
        border-radius: 8px;
        transition: background-color .5s ease-in-out;
    }

    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        .open-link, .print-link, a[download] {
            pointer-events: none;
            opacity: .4;
        }
    }

    span.options:after {
        display: block;
        content: "";
        clear: both;
    }

    span.options form#position-form {
        float: right;
        width: 400px;
        max-width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }

    span.options form#position-form > * {
        height: 38px;
        box-sizing: border-box;
    }

    span.options form#position-form [type="submit"] {
        margin-left: 5px;
    }

    span.options form#position-form [type="submit"]:before {
        font-family: fontAwesome, sans-serif;
        content: '\f00c';
    }

    #position-result {
        position: absolute;
        left: 1px;
        bottom: -17px;
        height: auto !important;
        font-weight: bold;
        font-size: 12px;
        transition: opacity 2s ease-in-out;
    }

    #position-result.success {
        color: #026d02;
    }

    #position-result.false {
        color: #ee0000;
    }

    #position-result.disappear {
        opacity: 0;
    }

    @media (max-width: 767px) {
        footer.footer .container-inner div.socials {
            left: -65px !important;
            right: initial !important;
            top: 0 !important;
            transform: scale(.4);
        }
    }

    @media screen and (max-width: 599px) {
        span.options form#position-form {
            float: none;
            width: 100%;
            margin-top: 15px;
        }
    }

    .pointer {
        cursor: pointer;
    }

    .modal-container.position {
        background-color: rgba(0, 0, 0, .75);
    }

    .options a {
        margin: 0 15px !important;
    }

    #previews canvas {
        border: 1px solid #000;
        -webkit-box-shadow: 7px 7px 5px 0 rgba(50, 50, 50, .15);
        -moz-box-shadow: 7px 7px 5px 0 rgba(50, 50, 50, .15);
        box-shadow: 7px 7px 5px 0 rgba(50, 50, 50, .15);
    }

    span.delimiter {
        width: 1px;
        height: 45px;
        background-color: #a0a0a0;
        margin: 0 10px;
    }

    @media screen and (max-width: 767px) {
        /*.options a {
            margin: 0 15px;
        }*/
        .options {
            justify-content: center !important;
        }

        .options a {
            margin: 0 10px 10px !important;
        }
    }

    @media screen and (max-width: 500px) {

        .options {
            display: block !important;
            text-align: center;
            margin-top: 30px !important;
        }

        .options .delimiter {
            display: block;
            width: 215px;
            height: 1px;
            margin: 15px auto;
        }

        .options a {
            margin: 0 15px !important;
        }
    }

</style>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Название файла</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($pdfInvoiceFileUrl || $excelInvoiceFileUrl): ?>
        <tr style="vertical-align: top">
            <td>1</td>
            <td>
                <?php if ($pdfInvoiceFileUrl): ?>
                    <a href="<?= $pdfInvoiceFileUrl ?>" target="_blank" class="open-link">
                    <? else: ?>
                    <a>
                <?php endif; ?>

                    <?= basename($pdfInvoiceFileUrl) ?>
                    &nbsp;
                    (<?= $fileSize ?>)
                </a>

                <span class="options" style="display: flex; align-items: flex-start; flex-wrap: wrap; font-size: 20px; margin-top: 15px;">

                    <?php if($pdfInvoiceFileUrl): ?>
                        <a href="<?= $pdfInvoiceFileUrl ?>" target="_blank" class="open-link" title="Открыть">
                        <? else: ?>
                        <a style="opacity: .4">
                    <?php endif; ?>

                            <i class="fa fa-eye" aria-hidden="true"></i>
                            <span class="title">Открыть</span>
                        </a>

                    <?php if($pdfInvoiceFileUrl && !is_IE()): ?>

                        <a href="#" class="print-link" title="Распечатать">
                            <span class="loader">
                                <i class="fa fa-spinner fa-pulse"></i></span>
                        <? else: ?>
                            <a style="opacity: .4">
                    <?php endif; ?>

                        <i class="fa fa-print" aria-hidden="true"></i>
                        <span class="title">Печать</span>
                    </a>

                    <a <?= $a_inner ?> title="Оплата" style="cursor: pointer;">
                        <i class="fa fa-credit-card-alt <?= $pay_class ?>" aria-hidden="true"></i>
                        <span class="title">Оплата</span>
                    </a>

                    <span class="delimiter"></span>

                    <a href="<?= $pdfInvoiceFileUrl ?>" target="_blank" download title="Скачать">
                        <i class="fa fa-download" aria-hidden="true"></i>
                        <span class="title">Скачать Pdf</span>
                    </a>

                    <!--Эксель файл-->
                    <?php if ($excelInvoiceFileUrl): ?>
                        <a title="Скачать эксель файл со счетом" class="pointer" download 
                            href="<?= $excelInvoiceFileUrl ?>"
                        >
                            <i class='fa fa-file-excel-o' aria-hidden='true'></i>
                            <span class="title">Скачать Excel</span>
                        </a>
                    <?php endif; ?>
                    <!--Эксель файл-->

                    <span class="delimiter"></span>

                    <!-- Отправка письма с вложением-->
                    <!-- <a title="Отправить на почту руководителю" class="pointer"
                        onclick="openMailSender('mailsender-director')">

                        <i class="fa fa-envelope-o" aria-hidden="true" style="color: #6ca24a;"></i>

                        <span class="title">
                            Отправить
                            <br>
                            руководителю
                        </span>
                    </a> -->
                    <!--Отправка письма с вложением-->

                    <!--Отправка письма с вложением-->
                    <!-- <a title="Отправить на почту бухгалтеру" class="pointer"
                        onclick="openMailSender('mailsender-accountant')">

                        <i class="fa fa-envelope" aria-hidden="true" style="color: #cc8910;"></i>

                        <span class="title">
                            Отправить
                            <br>
                            бухгалтеру
                        </span>
                    </a> -->
                    <!--Отправка письма с вложением -->
                </span>
            </td>
        </tr>

    <?php else: ?>
        <tr>
            <td>-</td>
            <td>Файлы не найдены...</td>
            <td><i style="opacity: .4" class="fa fa-eye" aria-hidden="true"></i></td>
            <td><i style="opacity: .4" class="fa fa-download" aria-hidden="true"></i></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
