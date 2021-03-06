<?php
    $this->pageTitle .= $user->name.' '.$user->surname;
    $user->user_info = HUsers::getUserInfo($user->id);
    $avatar = UsersImages::model()->find('user = :user', array('user'=>$user->id));
    $profile_image =  $avatar !== NULL ? '/avatars/u'.$user->id.'/'.$avatar->filename : '/images/no_avatar.png';
?>

<div class="user_profile">


    <div class="left_block">

        <div class="avatar">
            <a href='<?php echo $profile_image; ?>'><img align="center" style="max-height: 222px; max-width: 222px; border: 1px solid #E7E7E7;" src="<?php echo $profile_image;?>"/>
        </div>

        <div class="profile_buttons">
            <?php echo $user->id == Yii::app()->user->id ? '<a class="btn" href="u'.$user->id.'/edit">'.Yii::t('profile','Edit profile').'</a>' : '';?>
            <?php HProfile::renderSendMessageButton($user); ?>
            <?php HProfile::renderFriendsButtons($user); ?>
            <a class="btn" href="/blog/<?php echo $user->id;?>"><?php echo Yii::t('blog', 'Blog');?></a>
            <?php echo $user->id == Yii::app()->user->id ? '<a class="btn" href="u'.$user->id.'/notes">'.Yii::t('notes','Notes').'</a>' : '';?>
            <?php


            $minds_count = '';

            if(Yii::app()->user->id == $user->id)
                $minds_count = ' +'.UsersMinds::model()->count('user=:user and new = 1',array(':user'=>Yii::app()->user->id));
            if($minds_count == 0)
                $minds_count ='';

            echo $user->id == Yii::app()->user->id ? '<a class="btn" href="/minds">'.Yii::t('minds','Minds').$minds_count.'</a>' : '<a class="btn" href="/minds/new/'.$user->id.'">'.Yii::t('minds','Leave your mind').'</a>'
            ?>
            <a class="btn" href="/u<?php echo $user->id;?>/materials"><?php echo Yii::t('materials', 'Materials');?></a>
        </div>

        <?php if (!empty($friends)) : ?>
        <div class="friends_block">

            <div class="friends_label" onclick="window.location.href='/u<?php echo $user->id?>/friends'">
                <a href="/u<?php echo $user->id?>/friends"><?php echo Yii::t('profile','Friends');?></a>
            </div>

            <table class='friends_table'>
                <tr>
                    <?php
                    $num_friends = count($friends);
                    for ($i = 0; $i < $num_friends; $i++)
                    {
                        if ($i != 0 && $i % 3 == 0 && $i != $num_friends)
                            echo '</tr><tr>';
                        if ($friends[$i]->id == $_GET['id'])
                            $friends[$i] = Users::model()->findByPk(Yii::app()->user->id);
                        $this->renderPartial('profile/_friend',array('friend'=>$friends[$i]));
                        if ($i == $num_friends - 1)
                            echo $num_friends % 3 == 1 ? '<td></td><td></td></tr>' : ($num_friends % 3 != 0 ? '<td></td></tr>' : '');
                    }
                    ?>
            </table>

        </div>
        <?php endif;?>
    </div>

    <div class="right_block">

        <div class="user_info">

            <div class="user_status">
                <?php
                if(time()-strtotime($user->last_update)<10*60)
                    echo '<div class="online"></div>';
                else
                    echo '<div class="offline"></div>';
                ?>
            </div>

            <div class="user_name">
                <?php echo CHtml::encode($user->name).' '.CHtml::encode($user->surname);?>
            </div>

            <?php

            foreach($user->user_info as $field)
            {
                $field->value = CHtml::encode($field->value);

                if($field->field == 8)
                    $field->value = Departments::model()->findByPk($field->value)->name;

                if($field->field == 9)
                    $field->value = '<a href="/users?group='.$field->value.'"><b>'.$field->value.'</b></a>';

                if($field->field == 10)
                    $field->value = date('d/m/Y',strtotime($field->value));

                if($field->field == 11)
                    $field->value == 1 ? $field->value=Yii::t('profile','female') : $field->value=Yii::t('profile','male');

                if($field->field <> 2 and $field->field <> 3)
                {
                    echo '<div class="label">'.$field->label.'</div>';
                    echo '<div class="about">'.$field->value.'</div>';
                }
            }
            ?>

        </div>

        <div class="wall">

            <?php

            $form = $this->beginWidget('CActiveForm', array(
                'id'=>'wall-records-form',
                'enableAjaxValidation'=>false,
            ));
            echo '<table><tr><td>';
            echo Yii::t('profile', 'Write on the wall').':';
            echo '</td></tr><tr><td>';
            echo $form->textArea(WallRecords::model(), 'text', array('placeholder'=>Yii::t('profile', 'Content'), 'class'=>'input_form'));
            echo '</td></tr><tr><td>';
            echo CHtml::submitButton(Yii::t('profile', 'Write'), array('style'=>'margin: 0px;'));
            echo '</td></tr></table>';
            $this->endWidget();
            $authors = array();

            $this->widget('CLinkPager',array(
                'pages'=>$pages,
                'maxButtonCount' => 1,
                'cssFile'=>'',
                'header' => '',
            ));

            foreach($wallRecords as $key=>$record)
                if(empty($authors[$record->user_from]))
                    $authors[$record->user_from] = Users::model()->findByPk($record->user_from);

            foreach($wallRecords as $key=>$record)
                $this->renderPartial('profile/wallRecord',array(
                    'authors'=>$authors,
                    'record'=>$record
                ));

            if (empty($wallRecords))
                echo Yii::t('profile', ($user->id == Yii::app()->user->id ? 'You' : 'User').' have no records yet.');
            ?>

        </div>

    </div>

</div>