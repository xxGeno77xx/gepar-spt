<div>
    <style>
        /* Loader styling */
        .loader {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border-top: 4px solid #FFF;
            border-right: 4px solid transparent;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }
    
        .loader::after {
            content: '';
            box-sizing: border-box;
            position: absolute;
            left: 0;
            top: 0;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border-bottom: 4px solid #FF3D00;
            border-left: 4px solid transparent;
        }
    
        /* Full-screen overlay styling */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        /* Flex container for centering content */
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px; /* Space between spinner and button */
        }
    
        /* Button styling */
        .cancel-btn {
            padding: 10px 20px;
            background-color: #FF3D00;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    
        .cancel-btn:hover {
            background-color: #D32F2F;
        }
    
        /* Keyframes for rotation animation */
        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
    
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    
        <div wire:loading>
            <div class="overlay">
                <div class="content">
                    <div class="loader"></div>
                    {{-- <button class="cancel-btn" onclick="cancelLoading">Annuler</button> --}}
                </div>
            </div>
        </div>
</div>
