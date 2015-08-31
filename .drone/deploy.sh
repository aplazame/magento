# Deploy to demo
ssh -i ~/.ssh/id_rsa.prod $DEPLOY_USER@aplazame.com "bash -c 'source ~/.bashrc;cd $MAGENTO_PATH;modgit update Magento_Aplazame'"
