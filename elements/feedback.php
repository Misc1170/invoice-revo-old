<style>
    #feedback-form {
        height: 34px;
        overflow: hidden;
        transition: height .5s ease-in-out;
    }

    #feedback-form.focused {
        height: 88px;
    }

    .modal-backdrop.show {
        display: none;
    }

    .modal.show {
        background-color: rgba(0, 0, 0, .5);
    }

    .modal-body h2{
        text-align: center;
        font-weight: 900;
        font-size: 48px;
        color: #1c631c;
    }
</style>

<form action="" class="validate-form" id="feedback-form" method="post">
    <div class="form-group">
        <input type="hidden" name="invoice" value="1">
        <input type="hidden" name="user_id" value="<?=  $fetch['InvoiceId'] ?>">
        <input type="hidden" name="name" value="invoice-comment">
        <textarea name="comment"
                  class="form-control"
                  id="feedback"
                  required
                  onfocus="$(this).closest('form').addClass('focused')"
                  onblur="$(this).closest('form').removeClass('focused')"
                  placeholder="Если у вас есть предложения по улучшению сайта, мы с удовольствием запишем их"></textarea>
    </div>
    <p align="right">
        <button class="btn btn-primary" type="submit">Отправить</button>
    </p>
</form>

<div class="modal" tabindex="-1" role="dialog" id="feedback-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="width: 100%; font-size: 16px;">
                    <b>Спасибо!</b>
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <h2>Спасибо за обратную связь</h2>
            </div>
        </div>
    </div>
</div>


<script src="/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>

<script>

   /* $(window).on('load', () => {
        const textarea = document.querySelector('textarea[name="comment"]');
       if(screen.width < 700){
           const placeholder = textarea.placeholder;
           textarea.placeholder = "";
           $(textarea).closest('.form-group').before(`<p class="placeholder-mobile">${placeholder}</p>`);
       }
    });*/

    $('#feedback-form').on('submit', e => {
        e.preventDefault();
        const form = e.target;
        const data = validate(form);

        if (!data)
            return false;

        data.method = 'setComment';

        const callback = response => {
            console.log(response);
            if(response.result){
                const modal = $('#feedback-modal');
                modal.modal('show');
                modal.find('.close, .close-btn').on('click', () => modal.modal('hide'));
                form.reset();
            }
        };

        fetchfunc('/assets/snippets/comments/Comments.php', callback, data);
    });
</script>

</div><!--button-inner-->