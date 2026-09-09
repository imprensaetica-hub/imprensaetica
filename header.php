<link href="models.css" rel="stylesheet" type="text/css">
<table width="100%" class="header_tm">
  <tr>
  
  <?php
  	$isMobile = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false);
  	echo ($isMobile) ? 
		"" 
	: '<td width=30% valign="center" align="center"><a href="https://www.facebook.com/profile.php?id=61581295814684" target="_blank"><img src="img/bc_70_fb.fw.png" width="30" height="30" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://x.com/ImprensaEtica" target="_blank"><img src="img/bc_70_x.fw.png" width="30" height="30" /></a><br /><font class="font_menu">Siga nossas redes sociais</font></td>';
 
  
    echo ($isMobile) ? '<td><img src="img/logo_princ.bc.fw.png" alt="Logo" width="234" height="60" /></td>' : '<td><img src="img/logo_princ.bc.fw.png" alt="Logo" /></td>';
	
	echo ($isMobile) ? 
		"" 
	: '<td width=30% valign="center" align="center"><a href="https://t.me/imprensaetica" target="_blank"><img src="img/bc_70_telegram.fw.png" width="30" height="30" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://whatsapp.com/channel/0029VbBM2RW7DAX7zTslm60s" target="_blank"><img src="img/bc_70_whatsapp.fw.png" width="30" height="30" /></a><br /><font class="font_menu">Siga nossos canais</font></td>';
  ?>    
  
  </tr>

  	<?php
	$isMobile = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false);
  	echo ($isMobile) ? 
		'<tr><td valign="bottom" align="center" width="30px"><a href="https://www.facebook.com/profile.php?id=61581295814684" target="_blank"><img src="img/bc_70_fb.fw.png" width="20" height="20" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://x.com/ImprensaEtica" target="_blank"><img src="img/bc_70_x.fw.png" width="20" height="20" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://t.me/imprensaetica" target="_blank"><img src="img/bc_70_telegram.fw.png" width="20" height="20" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://whatsapp.com/channel/0029VbBM2RW7DAX7zTslm60s" target="_blank"><img src="img/bc_70_whatsapp.fw.png" width="20" height="20" /></a></td></tr>' 
	: "";
  ?>

  <tr>

    <?php
  	$isMobile = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false);
  	echo ($isMobile) ? 
		"" 
	: "<td width=20%>&nbsp;</td>";
    ?>    

    <td align="center" valign="middle" height="50px">
     <nav class="font_menu">
        <a href="index.php" class="font_menu">&nbsp;&Iacute;ndice&nbsp;</a>|
        <a href="noticias.php" class="font_menu">&nbsp;Not&iacute;cias&nbsp;</a>|
        <a href="analises.php" class="font_menu">&nbsp;An&aacute;lises&nbsp;</a>
        
        <?php 
        $isMobile = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false);
        echo ($isMobile) ? "<br />" : "|";
        ?>    
        
        <a href="busca.php" class="font_menu">&nbsp;Busca&nbsp;</a>|
        <a href="about.php" class="font_menu">&nbsp;Sobre&nbsp;</a>|
        <a href="contato.php" class="font_menu">&nbsp;Contato&nbsp;</a>
      </nav>
      
    </td>
    
    <?php
  	$isMobile = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false);
  	echo ($isMobile) ? 
		"" 
	: "<td width=20%>&nbsp;</td>";
  ?>    
    
  </tr>
  
</table>