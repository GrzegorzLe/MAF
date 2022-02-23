<?php
$news = array( );
$news[ ] = "Promocyjne ceny wymiany opon.|Jeśli zgłosisz się do naszego zakładu z sezonową wymianą opon w miesiącu październiku, na hasło 'Polak mądr za wczasu' otrzymasz 10% zniżke na tą usłygę!";
$news[ ] = "Zniżka na generalny przegląd samochodu.|Sezon jesienno zimowy jest wymagający zarówno dla kierowców jak i ich pojazdów. Jeśli odwiedzisz nasz zakład do 15 października br. wykonamy pełny przegląd Twojego samochodu (m.in. sprawdzenie poziomu płynów, zbieżności kół, ustawienie świateł, kontrola akumulatora) ze zniżką 15% na usługę.";
$news[ ] = "Bezpłatna regulacja swiatel.|W miesiacach pazdzierniku i listopadzie po wczesniejszym kontakcie z nasza stacja kontroli wykonamy bezplatna regulacje swiatel.";
if ( $dsp == 'carousel' )
{
?>
<?php
}
elseif ( $dsp == 'miniBox' )
{
?>
<h3>Aktualnosci</h3>
<img />
<a>czytaj dalej...</a>
<?php
}
elseif ( $dsp == 'microBox' )
{
?>
<h4>Oferty Specjalne</h4>
<div class="newsBox"><ul>
<?php 
foreach( $news as $new )
{
	$new = explode( '|', $new );
	echo '<li><div class="newBox"><span class="newsTitle">' . $new[ 0 ] . '</span>' . $new[ 1 ] . '</div></li>';
}
?>
</ul></div>
<a href="/strona/aktualnosci" class="followUp"><span>czytaj dalej...</span></a>
<?php 
}
else
{
?>
<?php 
foreach( $news as $new )
{
	$new = explode( '|', $new );
	echo '<div class="horizBox"><h3>' . $new[ 0 ] . '</h3><div class="subBox">' . $new[ 1 ] . '</div></div>';
}
}
