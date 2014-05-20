<?php

echo '<table border="1" width="100%">';
		echo '<tr>';
		echo '<td width="30%"><b>Thời gian:</b></td>';
		echo '<td>'.$news['News']['date'].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><b>Tiêu đề:</b></td>';
		echo '<td>'.$news['News']['title'].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><b>Tóm tắt:</b></td>';
		echo '<td>'.$news['News']['desc'].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><b>Nội dung:</b></td>';
		echo '<td>'.$news['News']['content'].'</td>';
		echo '</tr>';
		echo '<tr>';

		echo '</table>';
		?>