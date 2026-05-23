import type { App } from "vue";

import Button from "primevue/button";
import Card from "primevue/card";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import Divider from "primevue/divider";
import InputText from "primevue/inputtext";
import Menu from "primevue/menu";
import Password from "primevue/password";
import ProgressBar from "primevue/progressbar";
import Select from "primevue/select";
import Skeleton from "primevue/skeleton";
import Tag from "primevue/tag";
import Toast from "primevue/toast";
import Tooltip from "primevue/tooltip";

export function registerPrimeComponents(app: App): void {
    app.component("Button", Button);
    app.component("Card", Card);
    app.component("Checkbox", Checkbox);
    app.component("Column", Column);
    app.component("ConfirmDialog", ConfirmDialog);
    app.component("DataTable", DataTable);
    app.component("Dialog", Dialog);
    app.component("Divider", Divider);
    app.component("InputText", InputText);
    app.component("PMenu", Menu);
    app.component("Password", Password);
    app.component("ProgressBar", ProgressBar);
    app.component("Select", Select);
    app.component("Skeleton", Skeleton);
    app.component("Tag", Tag);
    app.component("Toast", Toast);

    app.directive("tooltip", Tooltip);
}
