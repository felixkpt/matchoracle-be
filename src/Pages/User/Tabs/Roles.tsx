import AutoTable from "@/components/Autos/AutoTable"
import Str from "@/utils/Str"

const Roles = () => {

	// begin component common config
	const pluralName = 'Roles'
	const uri = '/auth/role-permissions/get-user-roles'
	const componentId = Str.slug(pluralName)
	const search = false
	const columns = [
		{
			label: 'ID',
			key: 'id',
		},
		{
			label: 'Role Name',
			key: 'name',
		},
		{
			label: 'Assigned',
			key: 'assigned',
		},
		{
			label: 'Status',
			key: 'Status',
		},
	]
	// end component common config

	return (
		<div>
			<div>
				<AutoTable
					baseUri={uri}
					columns={columns}
					search={search}
					tableId={`${componentId}Table`}
				/>
			</div>
		</div>
	);
};

export default Roles;