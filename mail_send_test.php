<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<form action="https://fluid-line.ru/invoice/mail-sender.php">
    <input type="email" name="email" placeholder="Email, на который отправить письмо со счетом">
    <input type="hidden" name="file" value="1b88d365a8e8514acdff9cad9fb5b5ae/unzipped/Заказ покупателя № 19066 от 19 мая 2021.pdf">
</form>



<a id="mail-sender" href="https://fluid-line.ru/invoice/mail-sender.php" data-href="" target="_blank">

</a>

<script>
    document.getElementById('mail-sender').addEventListener('click', function (e) {
        e.preventDefault();
        window.open(this.href + '?file='  +  this.dataset.href);
    });
</script>

</body>
</html>


?>