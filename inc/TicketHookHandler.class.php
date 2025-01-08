<?php

require_once 'IHookItemHandler.php';

class PluginSmartAssignTicketHookHandler extends CommonDBTM implements IPluginSmartAssignHookItemHandler {

    protected $DB;
    protected $rrAssignmentsEntity;

    public function __construct() {
        global $DB;

        $this->DB = $DB;
        $this->rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
    }

    public function itemAdded(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - Item Type: " . $item->getType());
        if ($item->getType() !== 'Ticket') {
            return;
        }
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - TicketId: " . $this->getTicketId($item));
        PluginSmartAssignLogger::addWarning(__METHOD__ . " - CategoryId: " . $this->getTicketCategory($item));
        $this->assignTicket($item);
    }

    protected function getTicketId(CommonDBTM $item) {
        return $item->fields['id'];
    }

    protected function getTicketCategory(CommonDBTM $item) {
        return $item->fields['itilcategories_id'];
    }

    public function getGroupsUsersByCategory($categoryId) {
        $sql = <<< EOT
                SELECT 
                    c.name AS Category,
                    c.completename AS CategoryCompleteName,
                    g.name AS 'Group',
                    gu.id AS UserGroupId,
                    gu.users_id AS UserId,
                    u.name AS Username,
                    u.firstname AS UserFirstname,
                    u.realname AS UserRealname
                FROM
                    glpi_itilcategories c
                        JOIN
                    glpi_groups g ON c.groups_id = g.id
                        JOIN
                    glpi_groups_users gu ON gu.groups_id = g.id
                        JOIN
                    glpi_users u ON gu.users_id = u.id
                WHERE
                    c.id = {$categoryId}
                ORDER BY gu.id ASC
EOT;
        $resultCollection = $this->DB->queryOrDie($sql, $this->DB->error());
        $resultArray = iterator_to_array($resultCollection);
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - result array: ', $resultArray);
        return $resultArray;
    }

    protected function assignTicket(CommonDBTM $item) {
        $itilcategoriesId = $this->getTicketCategory($item);
    
        if ($this->rrAssignmentsEntity->getOptionAutoAssignMode() === 0) {
            // Modo balanceamento ativo
            $groupId = $this->rrAssignmentsEntity->getGroupByItilCategory($itilcategoriesId);
    
            if ($groupId === false) {
                PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - Grupo não encontrado para a categoria: ' . $itilcategoriesId);
                return;
            }
    
            $extraCondition = '';
            if ($this->rrAssignmentsEntity->getOptionAutoAssignType() === 1) {
                $extraCondition = "AND t.itilcategories_id = {$itilcategoriesId}";
            }
    
            $sql = <<<EOT
                    SELECT tu.users_id, COUNT(t.id) AS active_tickets
                    FROM glpi_tickets_users tu
                    JOIN glpi_tickets t ON tu.tickets_id = t.id
                    JOIN glpi_groups_users gu ON tu.users_id = gu.users_id
                    JOIN glpi_groups_tickets gt ON t.id = gt.tickets_id
                    WHERE tu.type = 2
                    AND t.status NOT IN (5, 6)
                    AND t.is_deleted = 0
                    AND gu.groups_id = {$groupId}
                    AND gt.groups_id = {$groupId}
                    AND gt.type = 2
                    {$extraCondition}
                    GROUP BY tu.users_id
                    ORDER BY active_tickets ASC, tu.users_id ASC
                    LIMIT 1;
            EOT;
            $resultCollection = $this->DB->queryOrDie($sql, $this->DB->error());
            $resultArray = iterator_to_array($resultCollection);
    
            if (count($resultArray) === 0) {
                PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - Nenhum técnico disponível no grupo: ' . $groupId);
                return;
            }
    
            $userId = $resultArray[0]['users_id'];
        } else {
            // Modo rodízio
            if (($lastAssignmentIndex = $this->getLastAssignmentIndex($item)) === false) {
                PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - Nada a fazer (categoria desativada ou não configurada; índice: ' . $lastAssignmentIndex . ')');
                return;
            }
    
            $categoryGroupMembers = $this->getGroupsUsersByCategory($this->getTicketCategory($item));
            if (count($categoryGroupMembers) === 0) {
                PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - Categoria sem grupo ou grupo sem usuários');
                return;
            }
    
            $newAssignmentIndex = isset($lastAssignmentIndex) ? $lastAssignmentIndex + 1 : 0;
    
            if ($newAssignmentIndex > (count($categoryGroupMembers) - 1)) {
                $newAssignmentIndex = $newAssignmentIndex % count($categoryGroupMembers);
            }
    
            if ($this->rrAssignmentsEntity->getOptionAutoAssignType() === 1) {
                $this->rrAssignmentsEntity->updateLastAssignmentIndexCategoria($itilcategoriesId, $newAssignmentIndex);
            } else {
                $this->rrAssignmentsEntity->updateLastAssignmentIndexGrupo($itilcategoriesId, $newAssignmentIndex);
            }
    
            $userId = $categoryGroupMembers[$newAssignmentIndex]['UserId'];
        }
    
        // Atribuir o ticket ao técnico selecionado
        $ticketId = $this->getTicketId($item);
        $this->setAssignment($ticketId, $userId, $itilcategoriesId);
        return $userId;
    }
    

    protected function getLastAssignmentIndex(CommonDBTM $item) {
        $itilcategoriesId = $this->getTicketCategory($item);
        return $this->rrAssignmentsEntity->getLastAssignmentIndex($itilcategoriesId);
    }

    protected function setAssignment($ticketId, $userId, $itilcategoriesId) {
        /**
         * remove any previous user assignment
         */
        $sqlDelete_glpi_tickets_users = <<< EOT
            DELETE FROM glpi_tickets_users 
            WHERE tickets_id = {$ticketId} AND type = 2;
EOT;
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - sqlDelete_glpi_tickets_users: ' . $sqlDelete_glpi_tickets_users);
        $this->DB->queryOrDie($sqlDelete_glpi_tickets_users, $this->DB->error());

        /**
         * remove any previous group assignment
         */
        $sqlDelete_glpi_groups_tickets = <<< EOT
            DELETE FROM glpi_groups_tickets 
            WHERE tickets_id = {$ticketId};
EOT;
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - sqlDelete_glpi_groups_tickets: ' . $sqlDelete_glpi_groups_tickets);
        $this->DB->queryOrDie($sqlDelete_glpi_groups_tickets, $this->DB->error());

        /**
         * insert the new assignment, based on rr
         */
        $sqlInsert_glpi_tickets_users = <<< EOT
            INSERT INTO glpi_tickets_users (tickets_id, users_id, type, use_notification, alternative_email) VALUES ({$ticketId}, {$userId}, 2, 1, '');
EOT;
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - sqlInsert_glpi_tickets_users: ' . $sqlInsert_glpi_tickets_users);
        $this->DB->queryOrDie($sqlInsert_glpi_tickets_users, $this->DB->error());
 
        /**
         * insert the new assignment, based on rr
         */  
        $sqlUpdate_glpi_tickets = <<< EOT
            UPDATE glpi_tickets 
            SET status = 2 
            WHERE glpi_tickets.status = 1 AND glpi_tickets.id = {$ticketId};
EOT;
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - sqlUpdate_glpi_tickets: ' . $sqlUpdate_glpi_tickets);
        $this->DB->queryOrDie($sqlUpdate_glpi_tickets, $this->DB->error());

        // if auto group assign is enabled assign the group too

        if ($this->rrAssignmentsEntity->getOptionAutoAssignGroup() === 1) {
            $groups_id = $this->rrAssignmentsEntity->getGroupByItilCategory($itilcategoriesId);
            $sqlInsert_glpi_tickets_groups = <<< EOT
                    INSERT INTO glpi_groups_tickets (tickets_id, groups_id, type) VALUES ({$ticketId}, {$groups_id}, 2)
EOT;
            PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - sqlInsert_glpi_tickets_groups: ' . $sqlInsert_glpi_tickets_groups);
            $this->DB->queryOrDie($sqlInsert_glpi_tickets_groups, $this->DB->error());
        }
    }

    public function itemPurged(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - nothing to do');
    }

    public function itemDeleted(CommonDBTM $item) {
        PluginSmartAssignLogger::addWarning(__FUNCTION__ . ' - nothing to do');
    }

}
