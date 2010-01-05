<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja" dir="ltr">

<head>
{include file='components/head.tpl' title='ゲーマーズ図書館 - 個別ゲーム'}
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
<h2>{$game.title|mb_truncate:25:"...":true}</h2>

<div>
<p>
{if '' == $game.medium_image_url}
<img src="/img/no_image.png" alt="{$game.title}" />
{else}
<a href="{$game.large_image_url}" rel="nofollow">
<img src="{$game.medium_image_url}" alt="{$game.title}" />
</a>
{/if}
</p>
<table border="1" width="100%">
{if '' != $game.brand}
<tr>
 <th>ブランド</th>
 <td>{$game.brand}</td>
</tr>
{/if}
{if '' != $game.japanese_name}
<tr>
 <th>プラットフォーム</th>
 <td>{$game.japanese_name}</td>
</tr>
{/if}
{if '' != $game.release_at}
<tr>
 <th>発売予定日</th>
 <td>{$game.release_at}</td>
</tr>
{/if}
{if '' != $game.price}
<tr>
 <th>定価</th>
 <td>{$game.price}円</td>
</tr>
{/if}
{if '' != $game.amazon_price}
<tr>
 <th>アマゾン価格</th>
 <td>{$game.amazon_price|number_format}円</td>
</tr>
{/if}
{if '' != $game.lowest_new_price
 && $game.lowest_new_price != $game.amazon_price}
<tr>
 <th>新品最安値</th>
 <td>{$game.lowest_new_price|number_format}円</td>
</tr>
{/if}
{if '' != $game.lowest_used_price}
<tr>
 <th>中古最安値</th>
 <td>{$game.lowest_used_price|number_format}円</td>
</tr>
{/if}
</table>
</div>

<h2>レビュー</h2>

{foreach from=$game_reviews item=review}
<div>

<table>
<tr>
 <th>このゲームの評価</th>
 <td>{$review.rating}</td>
</tr>
<tr>
 <th>このレビューの評価</th>
 <td>{$review.total_vote}人中{$review.helpful_vote}人が「参考になった」と投票したレビューです。</td>
</tr>
<tr>
 <th>レビュー</th>
 <td><strong>{$review.title}</strong><br />{$review.article}<br />({$review.comment_at})</td>
</tr>

</table>

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

{* analytics *}
{include file='components/analytics.tpl'}

</body>

</html>
