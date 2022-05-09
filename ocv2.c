/*
 * Copyright 2011 ArtForz
 * Copyright 2011-2013 pooler
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.  See COPYING for more details.
 */

#include "cpuminer-config.h"
#include "miner.h"

#include <string.h>
#include <inttypes.h>


int scanhash_ocv2(int thr_id, uint32_t *pdata, const uint32_t *ptarget,
	uint32_t max_nonce, unsigned long *hashes_done)
{
	
	uint32_t hash[8];
	
	uint32_t reversed_hash[8];
	
	uint32_t debug_hash[8];

	uint32_t n = pdata[19] - 1;
	const uint32_t first_nonce = pdata[19];
	const uint32_t Htarg = ptarget[7];
	
	int i;
	
	uint32_t endian_blockheader[20];
	uint32_t tmp_nonce;
	
	tmp_nonce = n + 1;

		for (i=0; i < 19; i++) {
			be32enc(&endian_blockheader[i], pdata[i]);
		}	
		
		be32enc(&endian_blockheader[19], tmp_nonce);
	
	//some required allocations
	char alloc1[1782];
	char alloc2[1782];
	char alloc3[4];
	
	ocv2_init_image((char*)endian_blockheader,alloc1,alloc2,alloc3);		
	
	
	
	do {
		tmp_nonce = ++n;
		
		
		
		
		be32enc(&endian_blockheader[19], tmp_nonce);
		

		
		ocv2_calculate_hash((char*)endian_blockheader,alloc1,alloc2,alloc3,(char*)hash);	

/*
		ocv2_hash((char*)endian_blockheader,(char*)debug_hash);
		for(i=0;i<8;i++)		
			if(swab32(hash[i]) != debug_hash[7-i])			
					applog(LOG_ERR, "test hash mismatch!!!");
					
*/				
					
					
			
		
		if (swab32(hash[0]) <= Htarg) {
			pdata[19] = tmp_nonce;
			
		for(i=0;i<8;i++)		
			reversed_hash[i] = swab32(hash[(7-i)]);		
			
			if (fulltest(reversed_hash, ptarget)) {			
				
				*hashes_done = n - first_nonce + 1;
				return 1;
			}
		}
	} while (n < max_nonce && !work_restart[thr_id].restart);
	
	*hashes_done = n - first_nonce + 1;
	pdata[19] = n;
	return 0;
}
