<?php
$video='*.m4v,*.mp4,*.webm,*.mpg,*.mov,*.avi,*.rmvb,*.mkv,*.mpg,*.mpeg,*.flv,*.m4v';
$audio='*.aac,*.flac,*.m4a,*.mp3,*.ogg,*.wav';
$document='*.txt,*.*.pdf,*.potx,*.ppt,*.pptx,*.xls,*.xlsx,*.doc,*.docx';
$images='*.ai,*.*.cdr,*.psd*.,bmp,*.eps,*.gif,*.heic,*.icns,*.ico,*.jpeg,*.jpg,*.png,*.svg,*.tif,*.tiff,*.ttf,*.webp,*.base64,3fr,*.arw,*.cr2,*.cr3,*.crw,*.dng,*.erf,*.mrw,*.nef,*.nrw,*.orf,*.otf,*.pef,*.raf,*.raw,*.rw2,*.sr2,*.srw,*.x3f';

$defaultextstr = '';
$defaultextstr .= $images;
$defaultextstr .= ','.$document;
$defaultextstr .= ','.$audio;
$defaultextstr .= ','.$video;
echo $defaultextstr;
