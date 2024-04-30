import { publish } from "@/utils/events";

class AutoActions {

    protected modelDetails: any
    protected tableData: any
    protected navigate: any
    protected listSources?: any
    protected exclude?: any
    protected modalSize: any
    protected customModalId?: any
    protected isSingle?: boolean

    constructor(modelDetails: any, tableData: any, navigate: any, listSources?: any, exclude?: any, modalSize?: any, customModalId?: any, isSingle?: boolean) {

        this.modelDetails = modelDetails
        this.tableData = tableData
        this.navigate = navigate
        this.listSources = listSources
        this.exclude = exclude
        this.modalSize = modalSize
        this.customModalId = customModalId
        this.isSingle = isSingle

    }

    handleModalAction = (event: Event) => {

        event.preventDefault()

        const target = event.target as HTMLElement; // Narrow down the type to HTMLElement

        if (target?.classList.contains('autotable-modal-edit')) {
            this.handleEdit(event)
        } else if (target?.classList.contains('autotable-modal-update-status')) {
            this.handleStatusUpdate(event)
        } else {
            
            const id = target.getAttribute('data-id');
            const action = (target.getAttribute('data-action') || target.getAttribute('href'));

            if (!id || !action) return;

            const record = this.isSingle ? this.tableData : this.tableData.data.find((item: any) => item.id == id)

            publish('prepareModalAction', { modelDetails: this.modelDetails, record, action, listSources: this.listSources, modalSize: this.modalSize, customModalId: this.customModalId, classList: Array.from(event.target?.classList) })

        }

    };

    handleView = (event: Event) => {

        event.preventDefault()

        const target = event.target as HTMLElement; // Narrow down the type to HTMLElement

        const id = target.getAttribute('data-id');
        const action = target.getAttribute('href');

        if (!id || !action) return;

        const record = this.isSingle ? this.tableData : this.tableData.data.find((item: any) => item.id == id)

        publish('prepareView', { modelDetails: this.modelDetails, record, action, modalSize: this.modalSize, customModalId: this.customModalId, exclude: this.exclude })

    };

    handleEdit = (event: Event) => {

        event.preventDefault()

        const target = event.target as HTMLElement; // Narrow down the type to HTMLElement

        const id = target.getAttribute('data-id');
        const action = (target.getAttribute('data-action') || target.getAttribute('href'))?.replace(/\/edit/g, '');

        if (!id || !action) return;

        const record = this.isSingle ? this.tableData : this.tableData.data.find((item: any) => item.id == id)

        publish('prepareEdit', { modelDetails: this.modelDetails, record, action, listSources: this.listSources, modalSize: this.modalSize, customModalId: this.customModalId })

    };

    handleStatusUpdate = (event: Event) => {

        event.preventDefault()

        const target = event.target as HTMLElement; // Narrow down the type to HTMLElement

        const id = target.getAttribute('data-id');
        const action = target.getAttribute('href');

        if (!id || !action) return;

        const record = this.isSingle ? this.tableData : this.tableData.data.find((item: any) => item.id == id)

        publish('prepareStatusUpdate', { modelDetails: this.modelDetails, record, action, modalSize: this.modalSize })

    }

    handleNavigation = (event: Event) => {

        if (event.ctrlKey) return

        event.preventDefault()

        const target = event.currentTarget as HTMLElement; // Narrow down the type to HTMLElement

        const href = target.getAttribute('href')
        if (href) {
            this.navigate(href)
        }
    };

}

export default AutoActions