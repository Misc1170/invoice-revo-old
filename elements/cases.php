<?php

$cases_db_config = $config['databases']['cases']; 

$mysql_fl = new mysqli(
    $cases_db_config['host'] . ':' . $cases_db_config['port'], 
    $cases_db_config['user'], 
    $cases_db_config['password'],
    $cases_db_config['db']
);
$mysql_fl->query("set names utf8");

$sql_cases = <<<SQL
SELECT DISTINCT 
    `c`.`id` as `id`,
    `c`.`pagetitle`,
    `c`.`createdon` as `date`,
    `v`.`value` as `image`
 FROM `revo_modx_site_content` as `c`
    LEFT JOIN `revo_modx_site_tmplvar_contentvalues` as `v`
    ON `c`.`id` = `v`.`contentid`
 WHERE `tmplvarid` = 6
SQL;

$query_fl = $mysql_fl->query($sql_cases);
$cases = $query_fl->fetch_all(MYSQLI_ASSOC);
?>

<style>
    .vebinars-grids {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-gap: 20px;
        padding-bottom: 50px;
    }

    @media screen and (max-width: 999px) {
        .vebinars-grids {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media screen and (max-width: 767px) {
        .vebinars-grids {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media screen and (max-width: 599px) {
        .vebinars-grids {
            grid-template-columns: repeat(1, 1fr);
        }
    }

    .vebinars-grids .vebinar-grid-item {
        cursor: pointer;
        position: relative;
        padding: 15px 15px 35px 15px;
        border-radius: 10px;
        -webkit-box-shadow: 3px 3px 15px 0 rgba(50, 50, 50, 0.5);
        -moz-box-shadow: 3px 3px 15px 0 rgba(50, 50, 50, 0.5);
        box-shadow: 3px 3px 15px 0 rgba(50, 50, 50, 0.5);
        transition: all .2s ease-in-out;
    }

    .vebinars-grids .vebinar-grid-item .vebinar-date {
        font-weight: bold;
        color: green;
        text-align: right;
        margin: 0 0 10px;
        font-size: 12px;
    }

    .vebinars-grids .vebinar-grid-item .vebinar-image {
        max-width: 100%;
        max-height: 150px;
        margin: auto;
    }

    .vebinars-grids .vebinar-grid-item .vebinar-title {
        font-size: 18px;
        font-weight: bold;
        margin: 15px 0 25px;
    }

    .vebinars-grids .vebinar-grid-item .select-vebinar {
        border-radius: 4px;
        display: inline-block;
        font-family: Inter Medium, Inter Regular, sans-serif;
        font-size: 14px;
        padding: 8px 16px;
        border: none;
        cursor: pointer;
        outline: none;
        position: absolute;
        left: 15px;
        bottom: 15px;
        color: #FFF;
        font-weight: bold;
        background-color: #0096BB;
    }

</style>

<!--https://fluid-line.ru/-->
<hr>
<h2 align="center" style="font-weight: bold; font-size: 24px;">Реализованные проекты</h2>
<br>
<div class="vebinars-grids">
    <?php foreach ($cases as $case): ?>
        <div class="vebinar-grid-item" data-tag="case" data-id="<?= $case['id'] ?>">
            <!--<p class="vebinar-date"><? /*= date('d.m.Y', $case['date']) */ ?></p>-->
            <a href="https://fluid-line.ru?id=<?= $case['id'] ?>" target="_blank">
                <img src="<?= $case['image'] ?>" alt="" class="vebinar-image">
            </a>
            <h3 class="vebinar-title"><?= $case['pagetitle'] ?></h3>
            <a href="https://fluid-line.ru?id=<?= $case['id'] ?>" target="_blank">
                <button type="button" class="select-vebinar veninar-details" data-id="<?= $case['id'] ?>">
                    Подробнее
                </button>
            </a>
        </div>
    <?php endforeach; ?>
</div>

