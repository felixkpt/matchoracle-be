import Footer from '../Authenicated/Footer/Index';

interface Props {
	Component: React.ComponentType
}
const GuestLayout = ({ Component }: Props) => {
	return (
		<>
			<div id="layoutAuthentication" className='bg-secondary-subtle'>
				<main>
					<div className="container">
						<div className="row justify-content-center">
							<Component />
						</div>
					</div>
				</main>
				<Footer />
			</div>
		</>
	);
}

export default GuestLayout