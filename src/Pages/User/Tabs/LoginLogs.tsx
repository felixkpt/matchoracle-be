import AutoTable from "@/components/Autos/AutoTable"
import Str from "@/utils/Str"

const LoginLogs = () => {

	// begin component common config
	const pluralName = 'LoginLogs'
	const uri = '/auth/login-logs'
	const componentId = Str.slug(pluralName)
	const search = false
	const columns = [
		{
			label: 'Login ID',
			key: 'id',
		},
		{
			label: 'IP',
			key: 'ip',
		},
		{
			key: 'source',
		},
		{
			key: 'time',
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
	)
}

export default LoginLogs