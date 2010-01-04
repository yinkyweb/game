<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr">

<head>
{include file='components/head.tpl' title='game review'}
</head>

<body>

<!-- container -->
<div id="container">

<!-- header -->
<div id="header"><a href="/"><img src="/img/clear.gif" alt="" style="width: 386px; height: 52px;" /></a></div>
<!-- //header -->

<!-- top_navi -->
<div id="top_navi">
<h1>ゲームレビューしますよ</h1>
<p>
ふがふが。
</p>
</div>

<!-- //top_navi -->

<!-- left_box -->
<div id="left_box">
<h2>ゲーム情報</h2>
</p>

{foreach from=$new_games item=game}
<div>
<h3>{$game.title|mb_truncate:25:"...":true} <a href="http://www.amazon.co.jp/exec/obidos/ASIN/{$game.asin}/{$conf.amazon.associate_id}/ref=nosim/"><img src="/img/amazon.gif" width="20px" height="20px"></a></h3>
<p>
{if '' == $game.medium_image_url}
<img src="/img/no_image.png" alt="{$game.title}" />
{else}
<img src="{$game.medium_image_url}" alt="{$game.title}" />
{/if}
</p>
{$game.brand}<br />
{$game.default_price|number_format}<br />
{$game.amazon_price|number_format}<br />
{$game.lowest_new_price|number_format}<br />
{$game.lowest_used_price|number_format}<br />
{$game.japanese_name}<br />

{$game.release_at}<br />

</div>
{/foreach}

<p>&nbsp;</p>

</div>
<!-- //left_box -->

<!-- right_box -->
<div id="right_box">

<h3>ナビゲーション</h3>

<ul>
<li><a href="/">ナビゲーション入れていきますよ。</a></li>
</ul>

<p>&nbsp;</p>

</div>
<!-- //right_box -->

<!-- bottom_navi -->
<div id="bottom_navi">
<p>
Copyright(C) yinkyweb.org All Rights Reserved.
</p>

</div>

<!-- //bottom_navi -->

</div>
<!-- //container -->

</body>

</html>
