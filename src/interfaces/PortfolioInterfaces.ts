export interface ProjectSlideInterface {
    id: number | string
    image: string;
    caption: string;
    description: string;
}

export type ProjectType = {
    id: string
    [key: string]: any
}

export interface AboutType {
    id: string
    current_title: string;
    name: string;
    slogan: string;
    introduction: string;
    content: string;
    image: string
}
