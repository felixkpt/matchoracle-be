import PageHeader from '@/components/PageHeader'
import useAxios from '@/hooks/useAxios'
import { useState } from 'react'
import AutoTable from '@/components/AutoTable'
import AutoModal from '@/components/AutoModal'
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces'

type Props = {
    category: any
}

const Index = ({ category }: Props) => {

    const { get: get } = useAxios()

    const [modelDetails, setModelDetails] = useState({})

    const list_sources = {
        async categoryId() {
            const res = await get('/admin/posts/categories?all=1').then((res) => res)
            return res.data || [] as ListSourceInterface[];
        }
    };

    return (
        <div className=''>
            {
                category &&
                <div>

                    <PageHeader title={'Topics List'} action="button" actionText="Create Topic" actionTargetId="CreateTopicModal" permission='/admin/posts/categories/topics' />

                    <AutoTable
                        baseUri={`/admin/posts/categories/${category.slug}/topics`}
                        columns={[
                            {
                                label: 'ID',
                                key: 'id',
                            },
                            {
                                label: 'Topic Title',
                                key: 'title',
                            },
                            {
                                label: 'Topic Slug',
                                key: 'slug',
                            },
                            {
                                label: 'Action',
                                key: 'action',
                            }

                        ]}
                        getModelDetails={setModelDetails}
                        search={true}
                        list_sources={list_sources}
                    />
                    {
                        Object.keys(modelDetails).length > 0 && <><AutoModal modelDetails={modelDetails} actionUrl={`/admin/posts/categories/topics?category_id=${category.id}`} id='CreateTopicModal' list_sources={list_sources} /></>
                    }
                </div>

            }
        </div>
    )
}

export default Index