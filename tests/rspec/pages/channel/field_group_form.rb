class FieldGroupForm < ControlPanelPage
  element :name, 'input[name="group_name"]'
  elements :submit, 'button[value="save"]'

  def load
    visit '/system/index.php?/cp/fields/groups/create'
  end
end
