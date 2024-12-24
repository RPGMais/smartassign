<?php

class PluginSmartAssignITILCategoryHookHandler extends CommonDBTM implements IPluginSmartAssignHookItemHandler {

    public function itemAdded(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - Item Type: " . $item->getType());
        if ($item->getType() !== 'ITILCategory') {
            return;
        }
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - ITILCategoryId: " . $this->getItilCategoryId($item));
        $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
        $rrAssignmentsEntity->insertItilCategory($this->getItilCategoryId($item));
    }

    protected function getItilCategoryId(CommonDBTM $item) {
        return $item->fields['id'];
    }

    public function itemDeleted(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - Item Type: " . $item->getType());
        if ($item->getType() !== 'ITILCategory') {
            return;
        }
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - ITILCategoryId: " . $this->getItilCategoryId($item));
        $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
        $rrAssignmentsEntity->updateIsActive($this->getItilCategoryId($item), 0);
    }

    public function itemPurged(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - Item Type: " . $item->getType());
        if ($item->getType() !== 'ITILCategory') {
            return;
        }
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - ITILCategoryId: " . $this->getItilCategoryId($item));
        $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
        $rrAssignmentsEntity->deleteItilCategory($this->getItilCategoryId($item));
    }

}
