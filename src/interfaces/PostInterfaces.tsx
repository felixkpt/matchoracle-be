export interface PostInterface {
    id: string
    title: string
    content_short: string
    content: string
    image: string
    category: any
    category_id: string
    topic: any
    topic_id: string
    priority_number: number
    created_at: string
    updated_at: string
    status_id: string
}

export interface PostCategoryInterface {
    id: string
    name: string
    image: string
    category: any
    parent_category_id: string
    priority_number: number
    created_at: string
    updated_at: string
    status_id: string
}