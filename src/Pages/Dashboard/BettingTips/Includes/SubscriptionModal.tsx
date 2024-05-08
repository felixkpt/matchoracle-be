import React, { useEffect, useState } from 'react';
import Step1 from './SubscriptionSteps/Step1';
import Step2 from './SubscriptionSteps/Step2';
import Step3 from './SubscriptionSteps/Step3';
import Step4 from './SubscriptionSteps/Step4';
import { BettingStrategyInterface } from '@/interfaces/FootballInterface';
import AlertMessage from '@/components/AlertMessage';

const SubscriptionModal = () => {
    const bettingStrategies = [
        {
            id: 1,
            name: 'Flat Strategy',
            slogan: 'Small but sure!',
            amount: 15,
            advantages: [
                'Good profits',
                'Decent winning rate',
            ]
        },
        {
            id: 2,
            name: 'Recovery Strategy',
            slogan: 'Steady profits!',
            amount: 29,
            advantages: [
                'Good profits',
                'Decent winning rate',
            ]

        },
        {
            id: 3,
            name: 'Martingle Strategy',
            slogan: 'Lion\'s share!',
            amount: 79,
            advantages: [
                'Good profits',
                'Decent winning rate',
            ]
        },
    ];

    const [step, setStep] = useState(1);
    const [bettingStrategy, setBettingStrategy] = useState<BettingStrategyInterface | null>(null);

    useEffect(() => {
        if (!bettingStrategy && bettingStrategies) {
            setBettingStrategy(bettingStrategies[1])
        }

    }, [bettingStrategies])

    const continueButtonMessages = [
        'View subscription options',
        'Complete subscription',
        'Continue to checkout',
        'Finish!',
    ];

    const handleStepChange = (val: number) => {
        const newStep = step + val;
        if (newStep >= 1 && newStep <= continueButtonMessages.length) {
            setStep(newStep);
        } else {
            setStep(1)
            document.querySelector('#SubscribeModal .btn-close')?.click()
        }
    };

    const [paymentMethod, setPaymentMethod] = useState<string | undefined>(undefined);

    const [isDisabled, setIsDisabled] = useState<boolean>(false)

    useEffect(() => {
        if (step == 3) {
            setIsDisabled(!paymentMethod)
        }

    }, [step, paymentMethod])


    const renderCurrentStep = () => {
        switch (step) {
            case 1:
                return <Step1 />;
            case 2:
                return bettingStrategy ? <Step2 bettingStrategy={bettingStrategy} bettingStrategies={bettingStrategies} setBettingStrategy={setBettingStrategy} /> : null;
            case 3:
                return bettingStrategy ? <Step3 bettingStrategy={bettingStrategy} paymentMethod={paymentMethod} setPaymentMethod={setPaymentMethod} /> : null;
            case 4:
                return bettingStrategy && paymentMethod ? <Step4 bettingStrategy={bettingStrategy} paymentMethod={paymentMethod} /> : <div className='alert alert-light'>You have not specified a payment option.</div>;
            default:
                return <AlertMessage message='You have skipped one or more steps.' />;
        }
    };

    return (
        <div className="modal fade" id="SubscribeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div className="modal-dialog modal-lg">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="staticBackdropLabel">Unleash Your Winning Potential: Elevate Your Betting Game Today!</h5>
                        <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div className="modal-body">
                        <div className='cursor-default'>
                            {renderCurrentStep()}
                        </div>
                    </div>
                    <div className="modal-footer justify-content-between">
                        <button type="button" className="btn btn-secondary" onClick={() => handleStepChange(-1)}>{step > 1 ? 'Previous step' : 'Close'}</button>
                        <button type="button" className="btn btn-primary submit-button" disabled={isDisabled} onClick={() => handleStepChange(1)}>{continueButtonMessages[step - 1]}</button>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SubscriptionModal;
