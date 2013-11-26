<?php


class StoryController extends Controller
{
    public function actionStory()
    {
        $storyid = Yii::app()->input->get("id");
        $stories = Stories::model()->getStorySummarybyID($storyid);

        $gifts = Gifts::model()->findAllByStoryIdWithPledgeCount($storyid);

        $currentPledgeCart = array();
        if(isset(Yii::app()->session['pledgeCart']))
        {
            $currentPledgeCart = Yii::app()->session['pledgeCart'];
        }

        $this->render("/story/index/story", array(
            'stories' => $stories,
            'currentPledgeCart' => $currentPledgeCart,
            'gifts' => $gifts

        ));
    }

    private function _getApplicableShelters()
    {
        $shelters = array();
        if(Yii::app()->user->role == "admin")
        {
            $shelters = Shelters::model()->findAll();
        }
        elseif(Yii::app()->user->role == "city")
        {
            $cityCoordinators = CityCoordinators::model()->findAllByAttributes(array(
                'user_id' => Yii::app()->user->id
            ));

            $cityIds = array();
            foreach($cityCoordinators as $cc)
            {
                $cityIds[] = $cc->city_id;
            }

            if(!empty($cityIds))
            {
                $shelters = Shelters::model()->findAll(array(
                    'condition' => 't.city_id in ('.implode(",", $cityIds).')'
                ));
            }
        }
        elseif(Yii::app()->user->role == "shelter")
        {
            //now get a list of shelters they have access to
            $shelters = ShelterCoordinators::model()->findAllByAttributes(array(
                'user_id' => $userId
            ));
        }

        return $shelters;
    }


    public function actionIndex()
    {
        Yii::app()->clientScript->registerScriptFile('/js/list.min.js', CClientScript::POS_END);

        //fetch all stories based on logged in userId and shelter_coordinators mapped shelterId
        $shelters = $this->_getApplicableShelters();

        //trim the shelter IDs into a commma separated list for the next query
        $idList = '';
        foreach($shelters as $shelter) {
            $idList .= ', "' . $shelter->shelter_id . '"';
        }

        $stories = $this->loadStoryList(substr($idList, 1));

        $this->render("/story/index/main", array('stories' => $stories));
    }

    public function actionEdit()
    {
        Yii::app()->clientScript->registerCssFile('/css/selectize.bootstrap3.css');
        Yii::app()->clientScript->registerScriptFile('/js/selectize.min.js', CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.js', CClientScript::POS_END);

        $storyId = Yii::app()->input->get("id");
        $selectedShelterId = Yii::app()->input->get("shelterId");
        $story = Stories::model()->findByPk($storyId);
        $gifts = Gifts::model()->findAllByAttributes(array(
            'story_id' => $storyId
        ));

        $userId = Yii::app()->user->id;

        //now get a list of shelters they have access to
        $shelters = $this->_getApplicableShelters();

        //trim the shelter IDs into a commma separated list for the next query
        $idList = '';
        foreach($shelters as $shelter) {
            $idList .= ', "' . $shelter->shelter_id . '"';
        }

        $selectableShelters = array();
        if(!empty($idList))
        {
            //now get a list of stories where the story is mapped to any of these shelter IDs
            $selectableShelters = Shelters::model()->findAll(array(
                'condition' => '`t`.shelter_id in (' . substr($idList, 1) . ')'
            ));
        }
        else
        {
            $selectableShelters = Shelters::model()->findAll();
        }
        //fget their userId

        $this->render("/story/edit/main", array(
            'story' => $story,
            'gifts' => $gifts,
            'shelters' => $selectableShelters,
            'selectedShelterId' => $selectedShelterId,
            'storyId' => $storyId,
            'userId' => $userId,
            'currentGiftRequests' => $this->getCurrentGiftRequest($storyId)
        ));
    }


    /**
     * retrieves list of currently selected locations for dropoff
     *
     * @param int shelterId
     */
    private function getCurrentGiftRequest($storyId)
    {
        if (empty($storyId)) {
            return array();
        }
        $currentGiftRequests = array();

        $gifts = Gifts::model()->findAllByAttributes(array('story_id' => $storyId));

        foreach ($gifts as $gift) {
            $currentGiftRequests[] = array(
                'id' => $gift->gift_id,
                'description' => $gift->description
            );
        }

        return $currentGiftRequests;
    }


    public function actionDelete()
    {
        $storyId = Yii::app()->input->get("id");

        Stories::model()->deleteByPk($storyId);

        Yii::app()->user->setFlash('success', "Story Deleted");

        $this->redirect($this->createUrl("story/index"));
    }

    public function actionSave()
    {

        $storyId = Yii::app()->input->post("storyId");
        $shelterId = Yii::app()->input->post("shelterId");
        $creatorId = Yii::app()->input->post("creatorId");
        $fname = Yii::app()->input->post("fname");
        $lname = Yii::app()->input->post("lname");
        $gender = Yii::app()->input->post("gender");
        $assigned_id = Yii::app()->input->post("assignedId");
        $storyToTell = Yii::app()->input->post("story");
        $display_order = Yii::app()->input->post("displayOrder");
        $enabled = Yii::app()->input->post("enabled", 0);
        $addNew = ('' != Yii::app()->input->post("saveNewButton"));

        $giftDescriptions = Yii::app()->input->post("gifts", array());
        $giftsToDelete = Yii::app()->input->post("giftsToDelete");

        $giftIdsToDeleteArray = array();
        if(!empty($giftsToDelete))
        {
            $giftIdsToDeleteArray = json_decode($giftsToDelete);
        }

        $story = new Stories();
        if(!empty($storyId))
        {
            //update
            $story = Stories::model()->findByPk($storyId);
        } else {
            $story->date_created = new CDbExpression('NOW()');
        }

        $story->creator_id = $creatorId;
        $story->shelter_id = $shelterId;
        $story->fname = $fname;
        $story->lname = $lname;
        $story->assigned_id = $assigned_id;
        $story->gender = $gender;
        $story->story = $storyToTell;
        $story->display_order = $display_order;
        $story->enabled = $enabled;

        if($story->save())
        {

            //$this->pruneRemovedGiftRequests($story->story_id);
            //$this->saveNewGiftRequest($story->story_id);

            if(!empty($giftIdsToDeleteArray))
            {
                foreach($giftIdsToDeleteArray as $giftId)
                {
                    Gifts::model()->deleteByPk($giftId);
                }
            }

            foreach($giftDescriptions as $description)
            {
                $gift = new Gifts();
                $gift->story_id = $story->story_id;
                $gift->description = $description;
                $gift->date_created = new CDbExpression('NOW()');
                $gift->enabled = 1;
                $gift->save();
            }

            Yii::app()->user->setFlash('success', "Saved");
        }
        else
        {

            Yii::app()->user->setFlash('error', "Shelter wasnt saved!");
        }

        $redirectParams = array(
            'id' => $story->story_id
        );
        if($addNew)
        {
            $redirectParams = array(
                'id' => 0,
                'shelterId' => $shelterId
            );
        }

        $this->redirect($this->createUrl("story/edit", $redirectParams));
    }


    /**
     * removes any requested gifts from the database that have been
     * removed from the list within the GUI
     *
     * @param int storyId
     */
    private function pruneRemovedGiftRequests($storyId)
    {

        $currentGiftRequests = Yii::app()->input->post("giftRequests", array());

        $existingGiftRequests = Gifts::model()->findAllByAttributes(array('story_id' => $storyId));
        $existingGiftIds = array();

        foreach ($existingGiftRequests as $giftRequest) {
            $existingGiftIds[] = $giftRequest->gift_id;
        }

        foreach ($existingGiftIds as $giftId) {

            if (!in_array($giftId, $currentGiftRequests)) {
                Gifts::model()->deleteAllByAttributes(array('gift_id' => $giftId));
            }
        }
    }

    /**
     * saves any new requests entered in the spare fields
     *
     * @param int storyId
     */
    private function saveNewGiftRequest($storyId)
    {

        $description = Yii::app()->input->post("gift_description");
        if (strlen($description) == 0) {
            return;
        }

        $gift = new Gifts();

        $gift->story_id = $storyId;
        $gift->description = $description;
        $gift->date_created = new CDbExpression('NOW()');

        $gift->save();

    }

    private function loadStoryList($shelterIdList) {

     $query = 'select stories.story_id, fname, lname, cities.name as city, shelters.name as `shelter`, users.`email`
        from stories
        left join shelters on shelters.`shelter_id` = stories.`shelter_id`
        left join cities on cities.city_id = shelters.`city_id`
        left join users on users.`user_id` = stories.`creator_id`
        where shelters.shelter_id in (' . $shelterIdList . ')';

        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);

        return $command->queryAll();
    }
}
