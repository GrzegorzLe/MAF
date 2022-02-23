<?php
if ( $dsp == 'carousel' )
{
?>
<?php
}
elseif ( $dsp == 'miniBox' )
{
?>
<h3>Media socjalne</h3>
<div class="subBox">
<a href="http://www.facebook.com/AutoSulej" title="facebook.com/AutoSulej" target="_blank"><img src="/img/icons/facebook-icon.png" alt="Facebook" class="left icon" /></a>
<a href="http://www.twitter.com/AutoSulej" title="twitter.com/AutoSulej" target="_blank"><img src="/img/icons/twitter-icon.png" alt="Twitter" class="right icon" /></a>
</div>
<a href="/strona/media" class="followUp"><span>nie przegap promocji &gt;&gt;</span></a>
<?php
}
elseif ( $dsp == 'microBox' )
{
?>
<h4>Media socjalne</h4>
<div class="subBox">
<a href="http://www.facebook.com/AutoSulej" title="facebook.com/AutoSulej" target="_blank"><img src="/img/icons/facebook-icon.png" alt="Facebook" class="left icon" /></a>
<a href="http://www.twitter.com/AutoSulej" title="twitter.com/AutoSulej" target="_blank"><img src="/img/icons/twitter-icon.png" alt="Twitter" class="right icon" /></a>
</div>
<a href="/strona/media" class="followUp"><span>nie przegap promocji &gt;&gt;</span></a>
<?php 
}
else
{
?>
<div class="horizBox">
<h3>Facebook</h3>
<div class="subBox">
<img src="/img/content/o-firmie.jpg" class="right" alt="Widok zakładu" width="320" height="180" />
Polub naszą stronę na FaceBook-u, a nie ominie Cię żadna nowa promocja w zakładzie AutoSulej. Dodatkowo będziesz na na czasie ze wszystkimi nowinkami z naszego zakładu!
</div>
</div>
<div class="horizBox">
<h3>Twitter</h3>
<div class="subBox">
<img src="/img/content/o-firmie.jpg" class="right" alt="Widok zakładu" width="320" height="180" />
Podążaj za zakładem AutoSulej na Twitterze, a będziesz pierwszy w kolejce do skorzystania z najnowszych promocji!
</div>
</div>
<?php 
}
