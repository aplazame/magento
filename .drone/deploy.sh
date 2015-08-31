# Deploy to demo
ssh -i ~/.ssh/id_rsa.prod $DEPLOY_USER@aplazame.com "zsh -c 'source ~/.zshrc;cd $MAGENTO_PATH;modgit update Magento_Aplazame'"
