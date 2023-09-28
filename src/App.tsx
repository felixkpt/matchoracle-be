import './App.css';
import AuthenticatedLayout from './Layouts/Authenicated/AuthenticatedLayout';
import GuestLayout from './Layouts/Guest/GuestLayout';

function App() {
  const isAuthenticated = true; // Replace with your actual authentication logic

  return (
    <div className="App">
      {isAuthenticated ? (
        <AuthenticatedLayout />
      ) : (
        <GuestLayout />
      )}
    </div>
  );
}

export default App;
