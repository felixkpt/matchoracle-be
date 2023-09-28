import AutoTable from '@/components/AutoTable'
import { useState } from 'react'
import AutoModal from '@/components/AutoModal'
import PageHeader from '@/components/PageHeader'
import useListSources from '@/hooks/apis/useListSources'

type Props = {}

const Index = (props: Props) => {

    const [modelDetails, setModelDetails] = useState({})

    const {rolePermissions: list_sources } = useListSources()

    return (

        <div>
            <PageHeader title={'Users List'} action="button" actionText="Create User" actionTargetId="CreateUserModal" permission='admin.settings.users' />
            <AutoTable
                baseUri='/admin/settings/users'
                columns={[
                    {
                        label: 'ID',
                        key: 'id',
                    },
                    {
                        label: 'User Name',
                        key: 'name',
                    },
                    {
                        label: 'Roles',
                        key: 'Roles',
                    },
                    {
                        label: 'Created At',
                        key: 'created_at',
                    },
                    {
                        label: 'Action',
                        key: 'action',
                    },
                ]}
                getModelDetails={setModelDetails}
                search={true}
            />

            {
                modelDetails && <><AutoModal id={`CreateUserModal`} modelDetails={modelDetails} actionUrl='/admin/settings/users' list_sources={list_sources} /></>
            }

        </div>
    )
}

export default Index