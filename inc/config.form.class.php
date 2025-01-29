<?php

include ('../../../inc/includes.php');
require_once 'config.class.php';
require_once 'RRAssignmentsEntity.class.php';

class SmartAssignConfigFormClass extends CommonDBTM {

    // Property to store dependency
    private $rrAssignmentsEntity;

    public function __construct() {
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - constructor called');
        
        // Initialize dependency in constructor
        $this->rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
    }

    public function renderTitle() {
        $injectHTML = <<< EOT
                <p>
                    <div align='center'>
                        <h1>SmartAssign</h1>
                    </div>
                </p>
EOT;
        echo $injectHTML;
    }

    public function showFormSmartAssign() {
        global $CFG_GLPI, $DB;

        if (self::checkCentralInterface()) {
            PluginSmartAssignLogger::addWarning(__METHOD__ . ' - displaying content');
            self::displayContent();
        } else {
            echo "<div align='center'><br><img src='" . $CFG_GLPI['root_doc'] . "/pics/warning.png'><br>" . __("Access denied", 'smartassign') . "</div>";
        }
    }

    public static function checkCentralInterface() {
        $currentInterface = Session::getCurrentInterface();
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - current interface: ' . $currentInterface);
        return $currentInterface === 'central';
    }

    public function displayContent() {
        $auto_assign_group = Html::cleanInputText(self::getAutoAssignGroup());
        $auto_assign_type = Html::cleanInputText(self::getAutoAssignType());
        $auto_assign_mode = Html::cleanInputText(self::getAutoAssignMode());
        $settings = self::getSettings();

        // Generate CSRF token and store it in the session
        $csrfToken = Session::getNewCSRFToken();
        $_SESSION['_glpi_csrf_token'] = $csrfToken;

        echo "<div class='center'>";
        echo "<form name='settingsForm' action='config.form.php' method='post' enctype='multipart/form-data'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => $csrfToken]); // Use token stored in session
        echo "<table class='tab_cadre_fixe'>";
        
        // Form Title
        echo "<tr><th colspan='4'>" . __('Smart ticket distribution based on ITIL Category group', 'smartassign') . "</th></tr>";
        echo "<tr><th colspan='4'><hr /></th></tr>";

        echo "<tr><th colspan='4'>";
        echo __('Assign the ITIL Category group?', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_group' value='1'" . ($auto_assign_group ? " checked='checked'" : "") . "> " . __('Yes', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_group' value='0'" . (!$auto_assign_group ? " checked='checked'" : "") . "> " . __('No', 'smartassign') . "";
        echo "</th></tr>";

        echo "<tr><th colspan='4'>";
        echo __('Assign technician by ITIL Category or group?', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_type' value='1'" . ($auto_assign_type ? " checked='checked'" : "") . "> " . __('Category', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_type' value='0'" . (!$auto_assign_type ? " checked='checked'" : "") . "> " . __('Group', 'smartassign') . "";
        echo "<br><span style='font-size: 12px; color: #555;'>" . __('For category, the distribution is done equally within each category based on the group. For group, the distribution is done equally among categories with the same group.', 'smartassign') . "</span>";
        echo "</th></tr>";

        echo "<tr><th colspan='4'>";
        echo __('Assign technician by rotation or balancing?', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_mode' value='1'" . ($auto_assign_mode ? " checked='checked'" : "") . "> " . __('Rotation', 'smartassign') . "&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_mode' value='0'" . (!$auto_assign_mode ? " checked='checked'" : "") . "> " . __('Balancing', 'smartassign') . "";
        echo "<br><span style='font-size: 12px; color: #555;'>" . __('For rotation, distribution is equal. For balancing, distribution is based on the technician with fewer open tickets in the queue.', 'smartassign') . "</span>";
        echo "</th></tr>";

        echo "<tr><th colspan='4'><hr /></th></tr>";
        echo "<tr><th>" . __('ITIL Category', 'smartassign') . "</th><th>" . __('Group', 'smartassign') . "</th><th>" . __('Number of members', 'smartassign') . "</th><th>" . __('Configuration', 'smartassign') . "</th></tr>";

        foreach ($settings as $row) {
            $id = $row['id'];
            $itilcategories_id = $row['itilcategories_id'];
            $category_name = Html::cleanInputText($row['category_name']);
            $group_name = isset($row['group_name']) ? Html::cleanInputText($row['group_name']) : "<em>" . __('No group assigned', 'smartassign') . "</em>";
            $num_group_members = isset($row['num_group_members']) ? Html::cleanInputText($row['num_group_members']) : "<em>N/A</em>";
            $is_active = $row['is_active'];

            echo "<tr>";
            echo "<td>{$category_name} ({$itilcategories_id})</td>";
            echo "<td>{$group_name}</td>";
            echo "<td>{$num_group_members}</td>";
            echo "<td>";
            echo Html::hidden("itilcategories_id_{$id}", ['value' => $itilcategories_id]);
            echo "<input type='radio' name='is_active_{$id}' value='1' " . ($is_active ? "checked='checked'" : "") . "> " . __('Active', 'smartassign') . "&nbsp;&nbsp;";
            echo "<input type='radio' name='is_active_{$id}' value='0' " . (!$is_active ? "checked='checked'" : "") . "> " . __('Inactive', 'smartassign') . "";
            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='4'><hr/></td></tr>";
		echo "<tr><td colspan='4' style='text-align: right;'><input type='submit' name='save' class='submit' value=" . __('Save', 'smartassign') . ">&nbsp;&nbsp;<input type='submit' class='submit' name='cancel' value=" . __('Cancel', 'smartassign') . "></td></tr>";
        echo "</table>";
    }

    protected static function getSettings() {
        $instance = new PluginSmartAssignRRAssignmentsEntity();
        return $instance->getAll();
    }

    protected static function getAutoAssignGroup() {
        $instance = new PluginSmartAssignRRAssignmentsEntity();
        return $instance->getOptionAutoAssignGroup();
    }

    protected static function getAutoAssignType() {
        $instance = new PluginSmartAssignRRAssignmentsEntity();
        return $instance->getOptionAutoAssignType();
    }

    protected static function getAutoAssignMode() {
        $instance = new PluginSmartAssignRRAssignmentsEntity();
        return $instance->getOptionAutoAssignMode();
    }

    public function saveSettings() {
		// CSRF token validation
		if (!isset($_POST['_glpi_csrf_token']) || $_POST['_glpi_csrf_token'] !== $_SESSION['_glpi_csrf_token']) {
			die(__('Invalid CSRF token', 'smartassign'));
		}
		
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - POST: ' . print_r($_POST, true));
        $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();

        // Save options
        $rrAssignmentsEntity->updateAutoAssignGroup($_POST['auto_assign_group']);
        $rrAssignmentsEntity->updateAutoAssignType($_POST['auto_assign_type']);
        $rrAssignmentsEntity->updateAutoAssignMode($_POST['auto_assign_mode']);

        // Save all assignments
        foreach (self::getSettings() as $row) {
            $itilCategoryId = $_POST["itilcategories_id_{$row['id']}"];
            $newValue = $_POST["is_active_{$row['id']}"];
            $rrAssignmentsEntity->updateIsActive($itilCategoryId, $newValue);
        }
    }
}