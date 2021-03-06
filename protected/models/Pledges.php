<?php

/**
 * This is the model class for table "pledges".
 *
 * The followings are the available columns in table 'pledges':
 * @property string $pledge_id
 * @property string $gift_id
 * @property string $user_id
 * @property string $date_created
 * @property integer $delivered
 */
class Pledges extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pledges';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('gift_id, user_id, date_created, status', 'required'),
			array('gift_id, user_id', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('pledge_id, gift_id, user_id, date_created', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pledge_id' => 'Pledge',
			'gift_id' => 'Gift',
			'user_id' => 'User',
			'date_created' => 'Date Created',
			'delivered' => 'Delivered',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('pledge_id',$this->pledge_id,true);
		$criteria->compare('gift_id',$this->gift_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('delivered',$this->delivered);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pledges the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function hasPledge($giftId)
	{
		return $this->count("gift_id=:giftId", array("giftId" => $giftId)) > 0;
	}

	public function getAllPledgesForShelters($shelterIds = array())
	{
		$sql = "
        SELECT 
        	p.*,
        	u.email,
        	s.story,
        	s.assigned_id,
        	s.fname,
        	s.lname,
        	g.description
        FROM pledges p
        JOIN users u ON u.user_id = p.user_id
        JOIN gifts g ON g.gift_id = p.gift_id
		JOIN stories s ON s.story_id = g.story_id
		JOIN shelters sh ON sh.shelter_id = s.shelter_id";

		if(!empty($shelterIds))
		{
			$sql .= " WHERE sh.shelter_id IN (" . implode(",", $shelterIds) . ")";
		}

        $command = $this->dbConnection->createCommand($sql);
        return $command->queryAll();
	}
}
