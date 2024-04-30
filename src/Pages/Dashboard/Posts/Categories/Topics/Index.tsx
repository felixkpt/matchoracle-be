import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces';
import PageHeader from '@/components/PageHeader';
import useAxios from '@/hooks/useAxios';

const Index = () => {
    const [modelDetails, setModelDetails] = useState({})
    const { get } = useAxios()

    const listSources = {
        async categoryId() {
            const res = await get('/dashboard/posts/categories?all=1').then((res) => res)
            return res.data || [] as ListSourceInterface[];
        }
    };

    return (
        <div>
            <PageHeader title={'Topics List'} action="button" actionText="Create Topic" actionTargetId="AutoModal" permission='/dashboard/posts/categories/topics' />
            <AutoTable
                baseUri='/dashboard/posts/categories/topics'
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
                        label: 'Created At',
                        key: 'Created_at',
                    },
                    {
                        label: 'Status',
                        key: 'Status',
                        is_html: true,
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
                modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/dashboard/posts/categories/topics' listSources={listSources} /></>
            }
        </div>
    );
};

export default Index;

