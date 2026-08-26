<div class="w-full h-[calc(100vh-48px)] overscroll-none items-center content-center justify-center"> 
	<div class="relative flex justify-center items-center">
		<div class="loader"></div>
		<img class="spinner-image" src="{{ asset('images/brand/favicon.png') }}" />
	</div>
	
	
	<style> 
		.loader { 
			border: 8px solid #d1d5db; /* Color gris claro */ 
			border-top: 8px solid #111827; /* Color azul */ 
			border-radius: 50%; 
			width: 80px; 
			height: 80px; 
			animation: spin 350ms linear infinite;
			display: flex; 
			justify-content: center; 
			align-items: center;
		} 

		.spinner-image { 
			position: absolute; 
			width: 40px; /* Ajustar el tamaño de la imagen */ 
			height: 44px;
		}

		@keyframes spin { 
			0% { transform: rotate(0deg); } 
			100% { transform: rotate(360deg); } 
		} 
	</style>
</div>
