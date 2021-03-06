<?php

    class MaterialsController extends Controller
    {

        public function filters()
        {
            return array(
                'accessControl', // perform access control for CRUD operations
                'postOnly + delete', // we only allow deletion via POST request
            );
        }

        public function accessRules()
        {
            return array(
                array('allow',
                    'actions'=>array('index','upload','createfolder','folder','deletefile'),
                    'users'=>array('@'),
                ),

                array('deny',
                    'users'=>array('*'),
                ),
            );
        }

        public function ActionIndex()
        {
            $this->render('index',array(
                'folders'=>MaterialsFolders::model()->findAll('user=:user',array(':user'=>Yii::app()->user->id)),
                'materials'=>MaterialsFiles::getUserFiles(Yii::app()->user->id),
            ));
        }

        public function actionUpload()
        {
            $file = new MaterialsFiles;
            if(isset($_POST['MaterialsFiles']))
            {
                $file->attributes = $_POST['MaterialsFiles'];
                $file->F = CUploadedFile::getInstance($file,'image');
                $file->user = Yii::app()->user->id;
                $file->name = $file->name == '' ? $file->F->name : $file->name;
                $file->description = $file->description == '' ? 'description' : $file->description;
                $file->file = md5(time().$file->F->name).strstr($file->name,'.');
                $file->time = date('Y-m-d H:i:s',time());

                if($file->save()){
                    if(!is_dir(YII_ROOT.'/files/u'.Yii::app()->user->id))
                        mkdir(YII_ROOT.'/files/u'.Yii::app()->user->id, 0700);

                    $path = YII_ROOT.'/files/u'.Yii::app()->user->id.'/'.md5(time().$file->F->name).strstr($file->name,'.');

                    $file->F->saveAs($path);
                    $this->redirect('/materials');
                }

            }
            $this->render('upload',array(
                'folders'=>MaterialsFolders::model()->findAll('user=:user',array(':user'=>Yii::app()->user->id)),
                'model'=>$file
            ));
        }

        public function actionCreateFolder()
        {

            $model = new MaterialsFolders;
            if(isset($_POST['MaterialsFolders']))
            {
                $model->attributes=$_POST['MaterialsFolders'];
                $model->user = Yii::app()->user->id;
                $model->time = date('Y-m-d H:i:s',time());
                if($model->save())
                    $this->redirect('/materials');
            }

            $this->render('createfolder',array(
                'model'=>$model
            ));

        }

        public function actionFolder($id)
        {
            $folder = MaterialsFolders::model()->findByPk($id);
            if(!is_null($folder))
            {

                $model = new MaterialsComments();

                if(isset($_POST['MaterialsComments']))
                {
                    $model->attributes = $_POST['MaterialsComments'];
                    $model->user = Yii::app()->user->id;
                    $model->folder = $id;
                    $model->time =  date('Y-m-d H:i:s',time());
                    if($model->save())
                        $this->redirect('/materials/folder/'.$id);
                }

                if($folder->privacy == 1)
                   if((!UsersFriends::isFriends(Yii::app()->user->id,$folder->user) and ($folder->user!=Yii::app()->user->id)))
                       throw new CHttpException(403,'access denied');

                    $criteria=new CDbCriteria;
                    $criteria->condition = 'folder =:folder';
                    $criteria->order = 'time desc';
                    $criteria->params = array(
                        ':folder'=>$id,
                    );
                    $pages=new CPagination(MaterialsFiles::model()->count($criteria));
                    $pages->pageSize=10;
                    $pages->applyLimit($criteria);
                    $files =  MaterialsFiles::model()->findAll($criteria);

                    $comments = MaterialsComments::getComments($id);

                    $this->render('folder',array(
                        'model'=>$model,
                        'folder'=>$folder,
                        'files'=>$files,
                        'comments'=>$comments['comments'],
                        'pages'=>$comments['pages'],
                    ));

            }
            else
                throw new CHttpException(404,'Nothing found.');
        }

        public function actionDeleteFile($id)
        {
            $file = MaterialsFiles::model()->findByPk($id);

            if(is_null($file))
                throw new CHttpException(404,'Nothing found');

            if($file->user == Yii::app()->user->id)
            {
                $file->delete();
            }

            $this->redirect('/materials');
        }

    }


?>
