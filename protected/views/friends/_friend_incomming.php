<a href="/u<?php echo $friend->id;?>"><?php echo $friend->login; ?></a> <a class="btn" href="/friends/accept/<?php echo $friend->id?>"><?php echo Yii::t('friends', 'Accept request')?></a> <a class="btn" href="/friends/reject/<?php echo $friend->id?>"><?php echo Yii::t('friends', 'Reject request')?></a>