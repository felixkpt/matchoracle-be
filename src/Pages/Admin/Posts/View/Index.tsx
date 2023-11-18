import PageHeader from '@/components/PageHeader'
import useAxios from '@/hooks/useAxios'
import useLoadAssets from '@/hooks/useLoadAssets'
import { useEffect, useRef, useState } from 'react'
import { useParams } from 'react-router-dom'

import Prism from "prismjs";
import '@/assets/prismjs/prism.css';
import '@/assets/prismjs/prism';
import { baseURL } from '@/utils/helpers'

type Props = {}

interface Posts {
    id: string
    title: string
    content: string
    content_short: string
    image: string
}

const Index = (props: Props) => {


    const ref = useRef<HTMLDivElement>(null);
    useEffect(() => {
        if (ref.current) {

            useEffect(() => {
                Prism.highlightAll();
            }, []);

        }
    }, []);

    const { id } = useParams()

    const { data, loading, get } = useAxios()

    const { loadImage } = useLoadAssets();

    const [imageUrl, setImageUrl] = useState()

    const [post, setPost] = useState<Posts>()

    useEffect(() => {

        if (id && !data)
            get(`admin/posts/view/${id}`)
        else {
            setPost(data?.data)
        }

    }, [id, data])

    return (
        <div className=''>

            {
                !loading && post &&

                <div>
                    <PageHeader title={post.title} action="link" actionText="Edit Post" actionLink={`/admin/posts/view/${post.id}/edit`} permission='/admin/posts/view/{id}' method='put' listUrl='/admin/posts' />
                    <div className='row mb-4'>
                        <div className='col-12 gap-2 row d-md-inline'>
                            {/* {post.image &&
                                <img style={{ maxWidth: '320px', height: 240 }} src={baseURL(post.image)} alt={`${post.title} featured image`} className='border featured-image p-2 me-md-5 col-12 col-md-4 mb-2 rounded mx-auto' />
                            } */}
                            <div className='post-content col-12' dangerouslySetInnerHTML={{ __html: String(post.content) }}></div>
                        </div>
                    </div>
                </div>

            }
        </div>
    )
}

export default Index