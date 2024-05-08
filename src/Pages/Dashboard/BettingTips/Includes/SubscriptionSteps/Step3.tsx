import React from 'react';
import { BettingStrategyInterface } from "@/interfaces/FootballInterface";
import './Step3.scss'; // Import CSS file for styling

type Props = {
    bettingStrategy: BettingStrategyInterface;
    paymentMethod: string | undefined
    setPaymentMethod: React.Dispatch<React.SetStateAction<string | undefined>>
};

const Step3 = ({ bettingStrategy, paymentMethod, setPaymentMethod }: Props) => {

    return (
        <div>
            <h6 className='text-muted'>Checkout!</h6>
            <div>
                <p>Selected Betting Strategy:</p>
                <p>Name: {bettingStrategy.name}</p>
                <p>Slogan: {bettingStrategy.slogan}</p>
                <p>Amount: ${bettingStrategy.amount}/mo</p>
                <p>Advantages:</p>
                <ul>
                    {bettingStrategy.advantages.map((advantage, index) => (
                        <li key={index}>{advantage}</li>
                    ))}
                </ul>
            </div>
            <div className="payment-method-container">
                <p>Select Payment Method:</p>
                <label className="payment-method">
                    <input type="radio" value="card" checked={paymentMethod === 'card'} onChange={(e) => setPaymentMethod(e.target.value)} />
                    <span className="checkmark"></span>
                    Card
                </label>
                <label className="payment-method">
                    <input type="radio" value="paypal" checked={paymentMethod === 'paypal'} onChange={(e) => setPaymentMethod(e.target.value)} />
                    <span className="checkmark"></span>
                    PayPal
                </label>
                <label className="payment-method">
                    <input type="radio" value="bitcoin" checked={paymentMethod === 'bitcoin'} onChange={(e) => setPaymentMethod(e.target.value)} />
                    <span className="checkmark"></span>
                    Bitcoin
                </label>
                <label className="payment-method">
                    <input type="radio" value="try" checked={paymentMethod === 'try'} onChange={(e) => setPaymentMethod(e.target.value)} />
                    <span className="checkmark"></span>
                    Request trial
                </label>
            </div>
        </div>
    );
};

export default Step3;
