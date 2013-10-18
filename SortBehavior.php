<?php
/**
 * $Author: xathloc $
 * $Date: 2013-08-28 01:51:07 +0200 (Śr) $
 * $Revision: 688 $
 * 
 * @copyright Copyright (c) 2013 Krzysztof Sobieraj (http://www.sobieraj.mobi)
 * @license   Commercial
 */

class SortBehavior extends ModelBehavior {
    public function setup(Model $model, $settings = array()) {
        if (!isset($this->settings[$model->alias])) {
            $this->settings[$model->alias] = array(
                'position_field' => 'position',
            );
        }
        $this->settings[$model->alias] = array_merge(
                $this->settings[$model->alias], (array) $settings);
    }

    public function arrange(Model $model, $data = array()) {
        if (empty($data))
            return false;

        if (array_key_exists('list', $data)) {
            for ($i = 0; $i < count($data['list']); $i++) {
                $model->id = $data['list'][$i];
                $model->saveField($this->settings[$model->alias]['position_field'], ($i + 1));
            }
        }
    }

    public function resort(Model $model) {
        $objects = $model->find('all', array('fields' => array('id'),
            'order' => array('position ASC'),));
        $counter = 1;
        foreach ($objects as $object) {
            $model->id = $object[$model->alias]['id'];
            $model->saveField($this->settings[$model->alias]['position_field'], $counter);
            $counter++;
        }
    }

    public function sort(Model $model, $direction, $position) {
        $oTarget = $model->findByPosition($position);
        $nCount = $model->find('count');

        if (isset($direction)) {
            if ($direction == 'up' && $position > 1) {
                $prev = $position - 1;
                $oNeighbour = $model->findByPosition($prev);
                $model->id = $oTarget[$model->alias]['id'];
//                $model->saveField('position', $prev);
                $model->saveField($this->settings[$model->alias]['position_field'], $prev);
                $model->id = $oNeighbour[$model->alias]['id'];
                $model->saveField($this->settings[$model->alias]['position_field'], $oTarget[$model->alias]['position']);
            } else if ($direction == 'down' && $position < $nCount) {
                $next = $position + 1;
                $oNeighbour = $model->findByPosition($next);
                $model->id = $oTarget[$model->alias]['id'];
                $model->saveField($this->settings[$model->alias]['position_field'], $next);
                $model->id = $oNeighbour[$model->alias]['id'];
                $model->saveField($this->settings[$model->alias]['position_field'], $oTarget[$model->alias]['position']);
            }
        }
    }

    public function setLast(Model $model, $id) {
        $data = $model->find('first', array('fields' => array($this->settings[$model->alias]['position_field']),
            'order' => array('position DESC')));
        debug($data);
        if ($data === null)
            $position = 1;
        else
            $position = $data[$model->alias]['position'] + 1;

        $model->id = $id;
        $model->saveField($this->settings[$model->alias]['position_field'], $position);
    }

}