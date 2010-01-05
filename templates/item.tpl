<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr">

<head>
{include file='components/head.tpl' title='ゲームスペース - 個別ゲーム'}
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

{foreach from=$game_reviews item=review}
<div>
{$review.amazon_customer_name}<br />
{$review.rating}<br />
{$review.helpful_vote}<br />
{$review.total_vote}<br />
{$review.title}<br />
{$review.article}<br />
comment_at: {$review.comment_at}<br />

</div>
{/foreach}

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
