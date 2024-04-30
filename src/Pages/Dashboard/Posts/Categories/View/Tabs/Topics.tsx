import PageHeader from '@/components/PageHeader'
import useAxios from '@/hooks/useAxios'
import { useState } from 'react'
import AutoTable from '@/components/Autos/AutoTable'
import AutoModal from '@/components/Autos/AutoModal'
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces'

type Props = {
    category: any
}

const Index = ({ category }: Props) => {

    const { get: get } = useAxios()

    const [modelDetails, setModelDetails] = useState({})

    const listSources = {
        async categoryId() {
            const res = await get('/dashboard/posts/categories?all=1').then((res) => res)
            return res.data || [] as ListSourceInterface[];
        }
    };

    return (
        <div className=''>
            {
                category &&
                <div>

                    <PageHeader title={'Topics List'} action="button" actionText="Create Topic" actionTargetId="CreateTopicModal" permission='/dashboard/posts/categories/topics' />

                    <AutoTable
                        baseUri={`/dashboard/posts/categories/${category.slug}/topics`}
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
                        listSources={listSources}
                    />
                    {
                        Object.keys(modelDetails).length > 0 && <><AutoModal modelDetails={modelDetails} actionUrl={`/dashboard/posts/categories/topics?category_id=${category.id}`} id='CreateTopicModal' listSources={listSources} /></>
                    }
                </div>

            }
        </div>
    )
}

export default Index