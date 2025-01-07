<?php

include ('../../../inc/includes.php');
require_once 'config.class.php';
require_once 'RRAssignmentsEntity.class.php';

class SmartAssignConfigFormClass extends CommonDBTM {

    // Propriedade para armazenar a dependência
    private $rrAssignmentsEntity;

    public function __construct() {
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - construtor chamado');
        
        // Inicializar a dependência no construtor
        $this->rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();
    }

    public function renderTitle() {
        $injectHTML = <<< EOT
                <p>
                    <div align='center'>
                        <h1>Configurações do SmartAssign</h1>
                    </div>
                </p>
EOT;
        echo $injectHTML;
    }

    public function showFormSmartAssign() {
        global $CFG_GLPI, $DB;

        if (self::checkCentralInterface()) {
            PluginSmartAssignLogger::addWarning(__METHOD__ . ' - exibir conteúdo');
            self::displayContent();
        } else {
            echo "<div align='center'><br><img src='" . $CFG_GLPI['root_doc'] . "/pics/warning.png'><br>" . __("Acesso negado") . "</div>";
        }
    }

    public static function checkCentralInterface() {
        $currentInterface = Session::getCurrentInterface();
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - interface atual: ' . $currentInterface);
        return $currentInterface === 'central';
    }

    public function displayContent() {
        $auto_assign_group = Html::cleanInputText(self::getAutoAssignGroup());
        $auto_assign_type = Html::cleanInputText(self::getAutoAssignType());
        $auto_assign_mode = Html::cleanInputText(self::getAutoAssignMode());
        $settings = self::getSettings();

        // Gerar o token CSRF e armazená-lo na sessão
        $csrfToken = Session::getNewCSRFToken();
        $_SESSION['_glpi_csrf_token'] = $csrfToken;

        echo "<div class='center'>";
        echo "<form name='settingsForm' action='config.form.php' method='post' enctype='multipart/form-data'>";
        echo Html::hidden('_glpi_csrf_token', ['value' => $csrfToken]); // Utiliza o token armazenado na sessão
        echo "<table class='tab_cadre_fixe'>";
        
        // Título do Formulário
        echo "<tr><th colspan='4'>Distribuição inteligente de chamados, com base no grupo encarregado da Categoria ITIL</th></tr>";
        echo "<tr><th colspan='4'><hr /></th></tr>";

        echo "<tr><th colspan='4'>";
        echo "Atribuir o grupo encarregado da Categoria ITIL? &nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_group' value='1'" . ($auto_assign_group ? " checked='checked'" : "") . "> Sim&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_group' value='0'" . (!$auto_assign_group ? " checked='checked'" : "") . "> Não";
        echo "</th></tr>";

        echo "<tr><th colspan='4'>";
        echo "Atribuição do tecnico por Categoria ou Grupo encarregado? &nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_type' value='1'" . ($auto_assign_type ? " checked='checked'" : "") . "> Categoria&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_type' value='0'" . (!$auto_assign_type ? " checked='checked'" : "") . "> Grupo";
        echo "<br><span style='font-size: 12px; color: #555;'>Quando categoria, a divisão é feita igualitariamente dentro de cada categoria, com base no grupo. Quando grupo, a divisão é feita igualitariamente entre categorias com o mesmo grupo.</span>";
        echo "</th></tr>";

        echo "<tr><th colspan='4'>";
        echo "Atribuição do tecnico por Rodizio ou Balanceamento? &nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_mode' value='1'" . ($auto_assign_mode ? " checked='checked'" : "") . "> Rodizio&nbsp;&nbsp;";
        echo "<input type='radio' name='auto_assign_mode' value='0'" . (!$auto_assign_mode ? " checked='checked'" : "") . "> Balanceamento";
        echo "<br><span style='font-size: 12px; color: #555;'>Quando Rodizio, a divisão é feita igualitariamente. Quando Balanceamento, a divisão é feita com base no tecnico com menos chamados abertos na fila.</span>";
        echo "</th></tr>";

        echo "<tr><th colspan='4'><hr /></th></tr>";
        echo "<tr><th>ITIL Category</th><th>Grupo</th><th>Número de Membros</th><th>Configuração</th></tr>";

        foreach ($settings as $row) {
            $id = $row['id'];
            $itilcategories_id = $row['itilcategories_id'];
            $category_name = Html::cleanInputText($row['category_name']);
            $group_name = isset($row['group_name']) ? Html::cleanInputText($row['group_name']) : "<em>Nenhum grupo atribuído</em>";
            $num_group_members = isset($row['num_group_members']) ? Html::cleanInputText($row['num_group_members']) : "<em>N/A</em>";
            $is_active = $row['is_active'];

            echo "<tr>";
            echo "<td>{$category_name} ({$itilcategories_id})</td>";
            echo "<td>{$group_name}</td>";
            echo "<td>{$num_group_members}</td>";
            echo "<td>";
            echo Html::hidden("itilcategories_id_{$id}", ['value' => $itilcategories_id]);
            echo "<input type='radio' name='is_active_{$id}' value='1' " . ($is_active ? "checked='checked'" : "") . "> Ativado&nbsp;&nbsp;";
            echo "<input type='radio' name='is_active_{$id}' value='0' " . (!$is_active ? "checked='checked'" : "") . "> Desativado";
            echo "</td>";
            echo "</tr>";
        }

        echo "<tr><td colspan='4'><hr/></td></tr>";
		echo "<tr><td colspan='4' style='text-align: right;'><input type='submit' name='save' class='submit' value=" . __('Salvar') . ">&nbsp;&nbsp;<input type='submit' class='submit' name='cancel' value=" . __('Cancelar') . "></td></tr>";
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
		// Validação do token CSRF
		if (!isset($_POST['_glpi_csrf_token']) || $_POST['_glpi_csrf_token'] !== $_SESSION['_glpi_csrf_token']) {
			die('Token CSRF inválido');
		}
		
        PluginSmartAssignLogger::addWarning(__METHOD__ . ' - POST: ' . print_r($_POST, true));
        $rrAssignmentsEntity = new PluginSmartAssignRRAssignmentsEntity();

        //Salvar opções)
        $rrAssignmentsEntity->updateAutoAssignGroup($_POST['auto_assign_group']);
        $rrAssignmentsEntity->updateAutoAssignType($_POST['auto_assign_type']);
        $rrAssignmentsEntity->updateAutoAssignMode($_POST['auto_assign_mode']);

        // Salvar todas as atribuições
        foreach (self::getSettings() as $row) {
            $itilCategoryId = $_POST["itilcategories_id_{$row['id']}"];
            $newValue = $_POST["is_active_{$row['id']}"];
            $rrAssignmentsEntity->updateIsActive($itilCategoryId, $newValue);
        }
    }
}
