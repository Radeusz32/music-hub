import type { App } from "vue";

import BaseInput from "@/Components/BaseInput.vue";
import BaseInputNumber from "@/Components/BaseInputNumber.vue";
import BasePassword from "@/Components/BasePassword.vue";
import BaseCheckbox from "@/Components/BaseCheckbox.vue";
import BaseDropdown from "@/Components/BaseDropdown.vue";
import BaseTextArea from "@/Components/BaseTextArea.vue";
import BaseMaskedInput from "@/Components/BaseMaskedInput.vue";
import BaseDialog from "@/Components/BaseDialog.vue";
import Tooltip from "@/Components/Tooltip.vue";

export function registerBaseComponents(app: App): void {
    app.component("BaseInput", BaseInput);
    app.component("BaseInputNumber", BaseInputNumber);
    app.component("BasePassword", BasePassword);
    app.component("BaseCheckbox", BaseCheckbox);
    app.component("BaseDropdown", BaseDropdown);
    app.component("BaseTextArea", BaseTextArea);
    app.component("BaseMaskedInput", BaseMaskedInput);
    app.component("BaseDialog", BaseDialog);
    app.component("Tooltip", Tooltip);
}
