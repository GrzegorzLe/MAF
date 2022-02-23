<?php
if ( $dsp == 'slideBox' )
{
?>
<div class="slideBox" id="oferta">
<h2 class="alpha60"><a href="/strona/oferta">Auto Naprawa Krzysztof Sulej</a></h2><br />
<h5 class="alpha60">Zakład swiadczy uslugi w zakresie napraw powypadkowych,<br />  napraw mechanicznych, diagnostyki komputerowej, serwisu ogumienia i autoholowania.</h5></div>
<?php
}
elseif ( $dsp == 'miniBox' )
{
?>
<h3>Usługi zakładu</h3>
<img src="/img/content/oferta-zewn.jpg" />
<a href="/strona/oferta" class="followUp"><span>Szczegóły oferty &gt;&gt;</span></a>
<?php
}
elseif ( $dsp == 'microBox' )
{
?>
<h4>Blacharka</h4>
<img />
<a>szczegóły oferty &gt;&gt;</a>
<?php 
}
else
{
?>
<h1>Blacharka</h1>
<ul>
<li>Naprawy blacharskie od drobnych „stłuczek” do poważnych uszkodzeń powypadkowych</li>
<li>Naprawy blacharskie skorodowanych elementów podwozia i nadwozia</li>
<li>Dokonujemy również napraw blacharskich samochodów ciężarowych</li>
<li>Specjalistyczna rama do napraw karoserii - „Unicar”</li>
<li>Naprawy układów wydechowych</li>
<li>Możliwość korekty punktów bazowych nadwozia</li>
<li>Zabezpieczenie antykorozyjne nadwozia i podwozia</li>
</ul><img />
<?php 
}
