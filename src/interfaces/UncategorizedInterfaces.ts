import { ReactNode } from "react"

export interface UserInterface {
    [x: string]: ReactNode;
    id: number;
    name: string;
    email: string;
    roles: []
}


export interface DataInterface {
    id: string | number
    name?: string
    status: boolean | number | string
    [key: string]: any
}
export interface CollectionItemsInterface {
    current_page: number
    data: DataInterface[]
    first_page_url: string
    from: number
    last_page: number
    last_page_url: string
    links: Link[]
    next_page_url: string
    path: string
    per_page: number
    prev_page_url: any
    to: number
    total: number
    model_name: string
    model_name_plural: string
    fillable: { [key: string]: any }
    sortable: []
    htmls: []
    query: string
    statuses: []
    module_uri: string
}

export interface Link {
    url?: string
    label: string
    active: boolean
}

export interface GetItemInterface {
    data: DataInterface
    model_name: string
    model_name_plural: string
    fillable: {}
    sortable: []
}

export interface GetItemsInterface {
    [index: number]: GetItemInterface[];
}

export interface ResponseDataInterface {
    type: string;
    message: string;
    data: any;
}

export interface ColumnInterface {
    label?: string
    key: string
    column?: string
    is_html?: boolean
}

interface ActionInterface {
    label: string;
    mode: 'buttons' | 'dropdown' | undefined;
    view?: 'modal' | 'page' | undefined;
    edit?: 'modal' | 'page' | undefined;
    delete?: boolean;
}

export interface AutoTableInterface {
    baseUri: string;
    listUri?: string;
    singleUri?: string;
    search?: boolean;
    columns: ColumnInterface[];
    exclude?: string[];
    action?: ActionInterface;
    reload?: number;
    hideCreate?: boolean
    getModelDetails?: (props: Omit<CollectionItemsInterface, 'data'>) => void
    list_sources?: { [key: string]: () => Promise<ListSourceInterface[]> }
    tableId?: string
    modalSize?: ModalSizeType
    customModalId?: string
    perPage?: number
}

export interface ListSourceInterface {
    id: string;
    name: string;
}

export interface DocsInterface {
    id: string
    title: string
    content: string
    content_short: string
    image: string
}

export type ModalSizeType = 'modal-sm' | 'modal-lg' | 'modal-xl';

export type HttpVerbsType = 'POST' | 'post' | 'PUT' | 'put' | 'PATCH' | 'patch' | 'DELETE' | 'delete'

export interface PageHeaderInterface {
    title: string;
    action?: 'button' | 'link';
    actionText?: string;
    actionLink?: string;
    permission?: string; // Permission name
    method?: string; // Method name
    actionTargetId?: string
    listUrl?: string
    setRecord?: React.Dispatch<React.SetStateAction<any>>
};

export interface TabInterface {
    name: string;
    label?: string;
    content: JSX.Element;
};
